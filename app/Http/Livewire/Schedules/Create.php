<?php

namespace App\Http\Livewire\Schedules;

use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class Create extends Component
{
    public User $currentChild;

    protected $listeners = [
        'parent.child-switched' => 'resetWhenChildSwitched',
    ];

    public function mount()
    {
        // $this->authorize('create', Schedule::class);
        $this->resetWhenChildSwitched();
    }

    public function resetWhenChildSwitched()
    {
        $parent = auth()->user();

        if (! $parent->isParent()) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $this->currentChild = $parent->parent_current_child;
    }

    public function fetchDates($period = 14)
    {
        $weekData = [];
        $dates = [];

        if (! now()->startOfWeek()->isSameDay(now())) {
            $diff = (int) ceil(now()->startOfWeek()->addWeek()->floatDiffInRealDays(now()));

            $period += $diff;
        }

        $latestScheduleWithTime = $this->currentChild->schedules()
            ->whereNotNull('start')
            ->whereNotNull('end')
            ->orderBy('date', 'DESC')
            ->first();

        for ($x = 0; $x < $period; $x++) {
            $current = now()->addDays($x);

            if (! $current->isWeekDay()) {
                if ($x != 0 && ! empty($weekData)) {
                    $lastValue = $weekData[count($weekData) - 1];

                    if (! empty($lastValue)) {
                        $weekData[] = [];
                    }
                }

                continue;
            }

            $day = $current->day;
            if ($day < 10) {
                $day = '0'.$day;
            }

            $text = $day.'. '.$current->shortMonthName;
            $date = $current->format('Y-m-d');

            $weekData[] = [
                'date' => $date,
                'start' => $latestScheduleWithTime ? $latestScheduleWithTime->start : 'XX:XX',
                'end' => $latestScheduleWithTime ? $latestScheduleWithTime->end : 'XX:XX',
                'status' => $latestScheduleWithTime ? 'pending' : 'approved',
                'available' => $this->userAvailability === 'available' ? true : false,
                'eats_onsite' => $this->userEatsOnsite,
                'user_id' => $this->currentChild->id,
            ];

            $dates[] = $date;
        }

        return [
            $dates,
            collect($weekData),
        ];
    }

    protected function needsTimeSchedule()
    {
        return $this->userAvailability === 'not-available-with-time';
    }

    public function getUserAvailabilityProperty()
    {
        $orgSettings = $this->currentChild->organization->settings;
        $userSettings = $this->currentChild->settings;

        $availability = $orgSettings['availability'];
        if (Arr::has($userSettings, 'availability')) {
            $availability = $userSettings['availability'];
        }

        return $availability;
    }

    public function getUserEatsOnsiteProperty()
    {
        $orgSettings = $this->currentChild->organization->settings;
        $userSettings = $this->currentChild->settings;
        $options = ['breakfast', 'lunch', 'dinner'];

        if (! $userSettings) {
            $userSettings = [];
        }

        $eatsOnsite = $orgSettings['eats_onsite'];
        if (Arr::has($userSettings, 'eats_onsite')) {
            foreach ($options as $option) {
                if (Arr::has($userSettings['eats_onsite'], $option) && gettype($userSettings['eats_onsite'][$option]) == 'boolean') {
                    $eatsOnsite[$option] = $userSettings['eats_onsite'][$option];
                } else {
                    $eatsOnsite[$option] = $orgSettings['eats_onsite'][$option];
                }

                if (! $orgSettings['eats_onsite'][$option]) {
                    $eatsOnsite[$option] = null;
                }
            }
        }

        return $eatsOnsite;
    }

    public function render()
    {
        $organization = $this->currentChild->organization;
        $limitations = $organization->settings['limitations'];
        $period = $limitations['selection_time'];

        list($dates, $weekData) = $this->fetchDates($period);

        $schedules = $this->currentChild->schedules()
            ->whereIn('date', $dates)
            ->get();

        $newWeekData = [];
        foreach ($weekData as $item) {
            if (empty($item)) {
                $newWeekData[] = $item;
            } else {
                $found = false;

                foreach ($schedules as $schedule) {
                    if ($schedule->date == $item['date']) {
                        $found = true;
                        $newWeekData[] = $schedule;
                    }
                }

                if (! $found) {
                    $newWeekData[] = Schedule::make($item);
                }
            }
        }

        return view('livewire.schedules.create', [
            'organization' => $organization,
            'group' => $this->currentChild->userGroup(),
            'schedules' => $newWeekData,
            'openingTimes' => $this->currentChild->organization->settings['opening_times'],
        ]);
    }
}
