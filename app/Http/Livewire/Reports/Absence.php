<?php

namespace App\Http\Livewire\Reports;

use App\Models\Schedule;
use App\Models\User;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Absence extends Component
{
    use HasSorting, HasIndexPageFilters, WithPagination;

    public $sortBy = '';
    public $sortOrder = 'ASC';
    public $year;

    protected $queryString = [
        'sortBy' => ['except' => ''],
        'sortOrder' => ['except' => 'ASC'],
        'page' => ['except' => 1],
    ];

    public $colFilters = [
        'sick_days' => [
            'filter' => null,
            'query' => null,
        ],
        'holiday_days_continuous' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'sick_days' => [
            'key' => 'sick_days',
            'normal' => false,
        ],
        'holiday_days_continuous' => [
            'key' => 'holiday_days_continuous',
            'normal' => false,
        ],
    ];

    protected $sortableCols = [
        'given_names',
        'sick_days',
        'cure_days',
        'holiday_days',
        'other_days',
        'holiday_days_continuous',
    ];

    public function mount()
    {
        $this->year = now();

        if (! $this->sortBy) {
            $this->sortBy = $this->sortableCols[0];
        }
    }

    public function nextYear()
    {
        $this->year->addYear();
    }

    public function prevYear()
    {
        $this->year->subYear();
    }

    public function getMainQueryProperty()
    {
        $user = auth()->user();

        $query = User::query()
            ->where('organization_id', $user->organization_id)
            ->where('role', 'User');

        $colFilters = collect($this->modelFilter);
        // $normalFilters = $colFilters->where('normal', true)->all();
        $extraFilters = $colFilters->where('normal', false)->all();

        $query->addSelect([
            'group_name' => DB::table('group_user')
                ->whereColumn('group_user.user_id', 'users.id')
                ->join('groups', 'groups.id', 'group_user.group_id')
                ->select('groups.name')
                ->limit(1),
            'group_uuid' => DB::table('group_user')
                ->whereColumn('group_user.user_id', 'users.id')
                ->join('groups', 'groups.id', 'group_user.group_id')
                ->select('groups.uuid')
                ->limit(1),
            'sick_days' => Schedule::query()
                ->whereColumn('schedules.user_id', 'users.id')
                ->whereYear('date', '=', $this->year->format('Y'))
                ->where('schedules.available', false)
                ->where('schedules.check_out', 'ill')
                ->selectRaw('COUNT(*)')
                ->limit(1),
            'cure_days' => Schedule::query()
                ->whereColumn('schedules.user_id', 'users.id')
                ->whereYear('date', '=', $this->year->format('Y'))
                ->where('schedules.available', false)
                ->where('schedules.check_out', 'cure')
                ->selectRaw('COUNT(*)')
                ->limit(1),
            'holiday_days' => Schedule::query()
                ->whereColumn('schedules.user_id', 'users.id')
                ->whereYear('date', '=', $this->year->format('Y'))
                ->where('schedules.available', false)
                ->where('schedules.check_out', 'vacation')
                ->selectRaw('COUNT(*)')
                ->limit(1),
            'other_days' => Schedule::query()
                ->whereColumn('schedules.user_id', 'users.id')
                ->whereYear('date', '=', $this->year->format('Y'))
                ->where('schedules.available', false)
                ->where('schedules.check_out', 'other')
                ->selectRaw('COUNT(*)')
                ->limit(1),
            'holiday_days_continuous' => DB::table('schedules', 's1')
                ->whereColumn('s1.user_id', 'users.id')
                ->whereYear('date', '=', $this->year->format('Y'))
                ->leftJoinSub($this->subQueryGet(), 'edgedates', function ($join) {
                    $join->on('s1.user_id', '=', 'edgedates.user_id');
                })
                ->select('edgedates.duration')
                ->limit(1),
        ]);

        if (count($extraFilters)) {
            $this->filterHavingScope($query, $extraFilters);
        }

        if ($this->sortBy) {
            $query->orderBy($this->sortBy, $this->sortOrder);
        }

        return $query;
    }

    private function subQueryGet()
    {
        $query = DB::query()
            ->select(
                'user_id',
                DB::raw('MAX(DATEDIFF(enddate, startdate)) AS duration')
            )
            ->from(
                DB::table('schedules', 's3')
                    ->select(
                        's3.user_id',
                        's3.date AS startdate',
                        DB::raw("IFNULL((SELECT MIN(s2.date) FROM schedules s2 WHERE s2.user_id = s3.user_id AND s2.date > s3.date AND s2.check_out != 'vacation'), s3.date) AS enddate"),
                        DB::raw("LAG(s3.check_out) OVER (PARTITION BY s3.user_id ORDER BY s3.date) AS last_check_out")
                    )
                    ->where('s3.check_out', 'vacation')
                    ->groupBy('s3.user_id', 's3.date')
            )
            ->groupBy('user_id');

        return $query;
    }

    public function render()
    {
        $users = $this->mainQuery
            ->paginate(config('setting.perPage'));

        return view('livewire.reports.absence', compact('users'));
    }
}
