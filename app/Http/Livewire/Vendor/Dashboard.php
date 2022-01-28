<?php

namespace App\Http\Livewire\Vendor;

use App\Http\Livewire\Component;
use App\Models\Organization;
use App\Models\Schedule;
use App\Models\User;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    use HasIndexPageFilters, HasSorting;

    public $placeholder = 'Search schedules .....';
    public $search;
    public $sortBy = '';
    public $sortOrder = 'ASC';

    public $viewType = 'daily';
    public $viewSetting = 'all';
    public $viewDate;

    public Organization $organization;

    protected $queryString = [
        'viewDate',
        'viewType',
        'viewSetting' => ['except' => 'all'],
        'sortBy' => ['except' => ''],
        'sortOrder' => ['except' => 'ASC'],
    ];

    protected $listeners = [
        'principalGroupUpdated' => '$refresh',
    ];

    protected $sortableCols = [
        'last_name',
        'group_name',
        'given_names',
    ];

    public $colFilters = [
        'user_group_name' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'user_group_name' => [
            'key' => 'name',
            'normal' => false,
        ],
    ];

    public function mount()
    {
        $viewDate = now();
        if (request()->has('viewDate') && request('viewDate')) {
            $viewDate = $this->parseDate(request('viewDate'));
        }

        $this->viewDate = $viewDate->format('Y-m-d');

        // if ($this->groups->first()) {
        //     $this->groupUuid = $this->groups->first()->uuid;
        // }

        if (request()->has('sortBy') && request('sortBy')) {
            $this->sortBy = request('sortBy');
        }

        $this->organization = auth()->user()->organization;
    }

    private function parseDate($date)
    {
        try {
            $date = Carbon::createFromFormat('Y-m-d', $date);
        } catch (\Throwable $th) {
            $date = now();
        }

        return $date;
    }

    public function previousDate()
    {
        $date = $this->parseDate($this->viewDate);

        if ($this->viewType === 'daily') {
            $this->viewDate = $date->subDay()->format('Y-m-d');
        } elseif ($this->viewType === 'weekly') {
            $this->viewDate = $date->subWeek()->format('Y-m-d');
        } else {
            $this->viewDate = $date->subMonth()->format('Y-m-d');
        }
    }

    public function nextDate()
    {
        $date = $this->parseDate($this->viewDate);

        if ($this->viewType === 'daily') {
            $this->viewDate = $date->addDay()->format('Y-m-d');
        } elseif ($this->viewType === 'weekly') {
            $this->viewDate = $date->addWeek()->format('Y-m-d');
        } else {
            $this->viewDate = $date->addMonth()->format('Y-m-d');
        }

        if ($date->isAfter(now()->endOfDay())) {
            $this->viewDate = now()->format('Y-m-d');
        }
    }

    public function getNextDisabledProperty()
    {
        $date = $this->parseDate($this->viewDate);

        if ($this->viewType === 'daily' && $date->isAfter(now()->startOfDay())) {
            return true;
        }

        if ($this->viewType === 'weekly' && $date->isAfter(now()->startOfWeek())) {
            return true;
        }

        if ($this->viewType === 'monthly' && $date->isAfter(now()->startOfMonth())) {
            return true;
        }

        return false;
    }

    private function getScheduleForDaily($users)
    {
        $date = $this->viewDate;

        $query = Schedule::query()
            ->where('date', $date)
            ->whereIn('user_id', $users)
            ->where('available', true)
            ->where('status', 'approved')
            ->addSelect([
                'group_name' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'schedules.user_id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.name')
                    ->limit(1),
                'user_uuid' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.uuid')
                    ->limit(1),
                'given_names' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.given_names')
                    ->limit(1),
                'last_name' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.last_name')
                    ->limit(1),
            ]);

        if ($this->viewType != 'summary') {
            $query = $this->applyFilters($query);
        }

        return $query->get();
    }

    private function applyFilters($query)
    {
        $colFilters = collect($this->modelFilter);
        $extraFilters = $colFilters->where('normal', false)->all();

        foreach ($extraFilters as $col) {
            if ($col['key'] == 'user_group_name') {
                $query->whereIn('user_id', function ($query) use ($col) {
                    $query->select('group_user.user_id')
                        ->from('group_user')
                        ->whereColumn('group_user.user_id', 'schedules.user_id')
                        ->join('groups', 'groups.id', 'group_user.group_id')
                        ->where('name', $col['operator'], $col['query']);
                });
            }
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortOrder);
        }

        return $query;
    }

    private function getScheduleForWeekly($users)
    {
        $date = $this->parseDate($this->viewDate);
        $from = $date->startOfWeek()->format('Y-m-d');
        $to = $date->endOfWeek()->format('Y-m-d');

        return $this->summaryQuery($users, $from, $to);
    }

    private function getScheduleForMonthly($users)
    {
        $date = $this->parseDate($this->viewDate);
        $from = $date->startOfMonth()->format('Y-m-d');
        $to = $date->endOfMonth()->format('Y-m-d');

        return $this->summaryQuery($users, $from, $to);
    }

    private function summaryQuery($users, $from, $to)
    {
        $query = Schedule::query()
            ->whereBetween('date', [$from, $to])
            ->whereIn('user_id', $users)
            ->where('available', true)
            ->where('status', 'approved')
            ->addSelect([
                'group_name' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'schedules.user_id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.name')
                    ->limit(1),
                'user_uuid' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.uuid')
                    ->limit(1),
                'given_names' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.given_names')
                    ->limit(1),
                'last_name' => DB::table('users')
                    ->whereColumn('users.id', 'schedules.user_id')
                    ->select('users.last_name')
                    ->limit(1),
            ]);

        if ($this->viewSetting != 'summary') {
            $query = $this->applyFilters($query);
        }

        return $query->get();
    }

    public function render()
    {
        $user = auth()->user();
        $usersQuery = User::query()
            ->where('organization_id', auth()->user()->organization_id)
            ->where('role', 'User');

        if ($user->isPrincipal()) {
            $principalGroup = $user->principal_current_group;

            $usersQuery->whereIn('users.id', function ($query) use ($principalGroup) {
                $query->select('group_user.user_id')
                    ->from('group_user')
                    ->where('group_user.group_id', $principalGroup->id);
            });
        }

        $users = $usersQuery->pluck('users.id')
            ->toArray();

        $userSetting = $user->settings;

        if ($user->role == 'Vendor') {
            $this->viewSetting = 'summary';

            if (Arr::has($userSetting, 'vendor_view')) {
                $this->viewSetting = $userSetting['vendor_view'];
            }
        }

        if ($this->viewType === 'daily') {
            $schedules = $this->getScheduleForDaily($users);
        } elseif ($this->viewType === 'weekly') {
            $schedules = $this->getScheduleForWeekly($users);
        } else {
            $schedules = $this->getScheduleForMonthly($users);
        }

        if ($this->viewSetting == 'summary') {
            $schedules = $this->makeScheduleData('group_name', $schedules);
        } else {
            if ($this->viewType != 'daily') {
                $schedules = $this->makeScheduleData('user_uuid', $schedules);
            }
        }

        $carbonDate = $this->parseDate($this->viewDate);

        if ($this->viewType === 'monthly') {
            $date = $carbonDate->format('F Y');
        } else {
            $date = $carbonDate->format(config('setting.format.date'));
        }

        return view('livewire.vendor.dashboard', compact('schedules', 'date'));
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $org = $this->organization;

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
    }

    private function makeScheduleData($groupBy, $schedules)
    {
        $newSchedulesData = [];

        foreach ($schedules->groupBy($groupBy) as $sKey => $schedulesItems) {
            $schedulesData = [
                'ukey' => $sKey,
                'total_breakfast' => 0,
                'total_lunch' => 0,
                'total_dinner' => 0,
                'allergies' => [],
            ];

            foreach ($schedulesItems as $idx => $schedule) {
                if ($idx == 0) {
                    $schedulesData['group_name'] = $schedule['group_name'];

                    if ($groupBy == 'user_uuid') {
                        $schedulesData['given_names'] = $schedule['given_names'];
                        $schedulesData['last_name'] = $schedule['last_name'];
                        $schedulesData['allergy'] = $schedule['allergy'];
                    }
                }

                $meals = $schedule->eats_onsite;

                if ($meals['breakfast']) {
                    $schedulesData['total_breakfast']++;

                    $schedulesData = $this->checkScheduleAndAllergy($schedulesData, $schedule->allergy, 'breakfast');
                }

                if ($meals['lunch']) {
                    $schedulesData['total_lunch']++;

                    $schedulesData = $this->checkScheduleAndAllergy($schedulesData, $schedule->allergy, 'lunch');
                }

                if ($meals['dinner']) {
                    $schedulesData['total_dinner']++;

                    $schedulesData = $this->checkScheduleAndAllergy($schedulesData, $schedule->allergy, 'dinner');
                }
            }

            $newSchedulesData[] = $schedulesData;
        }

        return $newSchedulesData;
    }

    private function checkScheduleAndAllergy($scheduleData, $allergy, $mealType)
    {
        if ($allergy) {
            if (Arr::has($scheduleData['allergies'], $mealType)) {
                if (Arr::has($scheduleData['allergies'][$mealType], $allergy)) {
                    $scheduleData['allergies'][$mealType][$allergy]++;
                } else {
                    $scheduleData['allergies'][$mealType][$allergy] = 1;
                }
            } else {
                $scheduleData['allergies'][$mealType][$allergy] = 1;
            }
        }

        return $scheduleData;
    }
}
