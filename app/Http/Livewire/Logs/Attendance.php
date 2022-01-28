<?php

namespace App\Http\Livewire\Logs;

use App\Http\Livewire\Component;
use App\Models\Attendance as AttendanceModel;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Attendance extends Component
{
    use WithPagination;

    public $date;
    public $search = '';
    public $showModal;
    public $events;

    protected $queryString = [
        'search' => ['except' => ''],
        'date',
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $date = now();
        if (request()->has('date') && request('date')) {
            $date = $this->parseDate(request('date'));
        }

        $this->date = $date->format('Y-m-d');

        $this->showModal = false;
        $this->events = collect();
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

    public function prevDay()
    {
        $date = $this->parseDate($this->date);
        $this->date = $date->subDay()->format('Y-m-d');
    }

    public function nextDay()
    {
        $date = $this->parseDate($this->date);
        $this->date = $date->addDay()->format('Y-m-d');
    }

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function showEvents($scheduleUuid)
    {
        $schedule = Schedule::findByUUIDOrFail($scheduleUuid);

        $this->events = AttendanceModel::query()
            ->where('schedule_id', $schedule->id)
            ->addSelect([
                'identifier' => Schedule::query()
                    ->whereColumn('attendances.schedule_id', 'schedules.id')
                    ->selectRaw('CONCAT(schedules.uuid, "-", attendances.id)')
                    ->limit(1),
                'trigger_person_name' => User::query()
                    ->whereColumn('attendances.triggred_id', 'users.id')
                    ->selectRaw('CONCAT(users.given_names, " ", users.last_name)')
                    ->limit(1),
                'trigger_person_role' => User::query()
                    ->whereColumn('attendances.triggred_id', 'users.id')
                    ->select('users.role')
                    ->limit(1),
            ])
            ->get();
        $this->showModal = true;
    }

    public function render()
    {
        $schedules = Schedule::query()
            ->whereIn('user_id', function ($query) {
                $query->select('u1.id')
                    ->from('users as u1')
                    ->where('u1.organization_id', auth()->user()->organization_id)
                    ->where('u1.role', 'User');

                if ($this->search) {
                    $query->join('group_user', 'group_user.user_id', 'u1.id')
                        ->join('groups', 'groups.id', 'group_user.group_id')
                        ->where(function ($q) {
                            $q->orWhere('groups.name', 'like', $this->search . '%')
                                ->orWhere('u1.given_names', 'like', $this->search . '%')
                                ->orWhere('u1.last_name', 'like', $this->search . '%');
                        });
                }
            })
            ->where('date', $this->date)
            ->addSelect([
                'attendances' => AttendanceModel::query()
                    ->whereColumn('attendances.schedule_id', 'schedules.id')
                    ->selectRaw('COUNT(*)'),
                'user_name' => User::query()
                    ->whereColumn('schedules.user_id', 'users.id')
                    ->selectRaw('CONCAT(users.given_names, " ", users.last_name)')
                    ->limit(1),
                'user_uuid' => User::query()
                    ->whereColumn('schedules.user_id', 'users.id')
                    ->select('users.uuid')
                    ->limit(1),
                'group_name' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'schedules.user_id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.name')
                    ->limit(1),
                'group_uuid' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'schedules.user_id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.uuid')
                    ->limit(1),
            ])
            ->orderBy('id', 'desc')
            ->paginate(config('setting.perPage'));

        $placeholder = trans('attendances.search');

        return view('livewire.logs.attendance', compact('schedules', 'placeholder'));
    }
}
