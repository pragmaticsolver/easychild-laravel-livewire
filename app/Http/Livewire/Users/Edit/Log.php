<?php

namespace App\Http\Livewire\Users\Edit;

use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserLog;
use Carbon\Carbon;
use Livewire\WithPagination;

class Log extends Component
{
    use WithPagination;

    public $user;
    public UserLog $userLog;
    public $showEditLogForm = false;
    public $endTime = '';
    public $visibleStartTime = null;
    public $visibleEndTime = null;
    public $startTime = null;
    public $maxEndTime = null;

    protected $queryString = [
        'page',
    ];

    public function mount(User $user)
    {
        $this->user = $user;
        $this->userLog = UserLog::make();
    }

    public function editLog(UserLog $userLog)
    {
        if (! $this->checkIfHasAuthorization($userLog)) {
            return;
        }

        $this->userLog = $userLog;
        $this->showEditLogForm = true;
        $this->endTime = '';
        $this->visibleStartTime = now()->setTimeFromTimeString($this->userLog->typeable->presence_start)->format('H:i');
        $this->visibleEndTime = now()->setTimeFromTimeString($this->userLog->typeable->presence_end)->format('H:i');

        $this->startTime = now()->setTimeFromTimeString($this->userLog->typeable->presence_start)->addMinutes(16)->format('H:i:s');

        $this->calculateMaxEndTime($this->userLog->typeable);
    }

    private function calculateMaxEndTime(Schedule $schedule)
    {
        $openingTimes = auth()->user()->organization->settings['opening_times'];

        $currentWeekDay = Carbon::parse($schedule->date)->dayOfWeek - 1;

        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        $max = '23:30';

        if ($openingTime) {
            $max = $openingTime['end'];
        }

        $this->maxEndTime = $max;
    }

    public function submitEditLog()
    {
        $this->validate([
            'endTime' => 'required|not_in:XX:XX',
        ]);

        if (! $this->checkIfHasAuthorization($this->userLog)) {
            return;
        }

        $createdDateTime = Carbon::parse($this->userLog->typeable->date)
            ->setTimeFromTimeString($this->endTime);

        $this->userLog->update([
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
            'created_at' => $createdDateTime,
        ]);

        $this->userLog->typeable->update([
            'presence_end' => $this->endTime,
        ]);
        $this->userLog = UserLog::make();

        $this->endTime = '';
        $this->visibleStartTime = null;
        $this->visibleEndTime = null;
        $this->showEditLogForm = false;

        $this->emitMessage('success', trans('users.log_section.update_success'));
    }

    private function checkIfHasAuthorization(UserLog $userLog)
    {
        if (! auth()->user()->isManager()) {
            return false;
        }

        if ($userLog->trigger_type != 'auto') {
            return false;
        }

        $schedule = $userLog->typeable;

        if (is_null($schedule->presence_start)) {
            return false;
        }

        if ($this->endTime) {
            $start = now()->setTimeFromTimeString($schedule->presence_start);
            $end = now()->setTimeFromTimeString($this->endTime);

            if ($start >= $end) {
                $this->emitMessage('error', trans('users.log_section.start_time_after_end'));

                return false;
            }
        }

        $userCount = User::query()
            ->where('organization_id', auth()->user()->organization_id)
            ->where('id', $userLog->user_id)
            ->count();

        if (! $userCount) {
            return false;
        }

        return true;
    }

    public function render()
    {
        $attendances = UserLog::query()
            ->where('user_id', $this->user->id)
            ->addSelect([
                'trigger_person_name' => User::query()
                    ->whereColumn('user_logs.triggred_id', 'users.id')
                    ->selectRaw('CONCAT(users.given_names, " ", users.last_name)')
                    ->limit(1),
                'trigger_person_role' => User::query()
                    ->whereColumn('user_logs.triggred_id', 'users.id')
                    ->select('users.role')
                    ->limit(1),
            ])
            ->latest('created_at')
            ->paginate(config('setting.perPage'));

        return view('livewire.users.edit.log', compact('attendances'));
    }
}
