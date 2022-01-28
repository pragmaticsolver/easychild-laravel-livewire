<?php

namespace App\Http\Livewire\Schedules;

use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use App\Traits\HasScheduleNotificationConcern;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class CheckOut extends Component
{
    use HasScheduleNotificationConcern;

    public $showModal = false;
    public $cause;
    public $start;
    public $end;
    public User $currentChild;

    protected $listeners = [
        'parent.child-switched' => 'resetValues',
    ];

    public function mount()
    {
        $this->resetValues();
    }

    public function resetValues()
    {
        $parent = auth()->user();

        if (! $parent->isParent()) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $this->currentChild = $parent->parent_current_child;

        $this->cause = $this->causes[count($this->causes) - 1];
    }

    public function getCausesProperty()
    {
        return config('setting.checkoutCauses');
    }

    public function open($date, $cause = null)
    {
        $this->resetValues();
        $this->start = $date;
        $this->end = $date;

        if ($cause) {
            $this->cause = $cause;
        }

        $this->showModal = true;
    }

    public function submit()
    {
        $start = Carbon::parse($this->start);
        $end = Carbon::parse($this->end);

        $currentDay = $start->copy();

        $dates = [];

        do {
            if ($currentDay->isWeekday()) {
                $allergy = null;
                if (Arr::has($this->currentChild->settings, 'allergies')) {
                    $allergy = $this->currentChild->settings['allergies'];
                }

                $schedule = Schedule::query()
                    ->where('user_id', $this->currentChild->id)
                    ->where('date', $currentDay->format('Y-m-d'))
                    ->first();

                if (! $schedule) {
                    $schedule = Schedule::create([
                        'user_id' => $this->currentChild->id,
                        'date' => $currentDay->format('Y-m-d'),
                    ]);
                }

                $updateData = [
                    'allergy' => $allergy,
                    'start' => null,
                    'end' => null,
                    'status' => 'pending',
                    'available' => false,
                    'check_out' => $this->cause,
                    'eats_onsite' => [
                        'breakfast' => false,
                        'lunch' => false,
                        'dinner' => false,
                    ],
                ];

                $schedule->update($updateData);

                if ($schedule->status == 'pending') {
                    $this->addNotificationToCustomJob($schedule);
                }

                // if ($schedule->status == 'approved') {
                //     $this->removeRelatedNotifications($schedule);
                // }

                $dates[] = $currentDay->format('Y-m-d');
            }

            $currentDay->addDay();
        } while ($currentDay->isSameDay($end) || $currentDay->isBefore($end));

        $this->dispatchBrowserEvent('schedules-create-item-refresh-check-out', [
            'dates' => $dates,
            'cause' => $this->cause,
            'status' => 'pending',
        ]);

        $this->emitMessage('success', trans('schedules.check_out.check_out_added_success'));

        $this->showModal = false;
    }

    public function cancel()
    {
        $this->showModal = false;

        $schedule = Schedule::query()
            ->where('user_id', $this->currentChild->id)
            ->where('date', $this->start)
            ->first();

        if ($schedule) {
            // dd('here', 'schedule-update-' . $this->currentChild->uuid . '-' . $this->start);

            $this->dispatchBrowserEvent('schedule-update-' . $this->currentChild->uuid . '-' . $this->start, [
                'uuid' => $schedule->uuid,
                'start' => $schedule->start,
                'end' => $schedule->end,
                'status' => $schedule->status,
                'available' => $schedule->available,
                'check_out' => $schedule->check_out,
                'eats_onsite' => $schedule->eats_onsite,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.schedules.check-out');
    }
}
