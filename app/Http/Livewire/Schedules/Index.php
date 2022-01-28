<?php

namespace App\Http\Livewire\Schedules;

use App\Http\Livewire\Component;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Index extends Component
{
    public $date = null;
    public $model = null;
    public $minTime = '00:00';
    public $maxTime = '23:30';
    public $min = 0;
    public $max = 23;

    public function mount($model = null)
    {
        $this->authorize('viewAny', [Schedule::class, $model]);

        $this->model = $model;

        $this->date = now()->format('Y-m-d');

        $this->setOpeningTimes();
    }

    public function prevDay()
    {
        $carbonDate = Carbon::parse($this->date);
        $carbonDate->subDay();
        $this->date = $carbonDate->format('Y-m-d');
    }

    public function nextDay()
    {
        $carbonDate = Carbon::parse($this->date);
        $carbonDate->addDay();
        $this->date = $carbonDate->format('Y-m-d');
    }

    private function setOpeningTimes()
    {
        $settings = $this->model->settings;

        if (! $settings) {
            $settings = $this->model->organization->settings;
        }

        $openingTimes = collect($settings['opening_times']);
        $currentKey = Carbon::parse($this->date)->dayOfWeek - 1;

        $openingTime = $openingTimes->where('key', $currentKey)->first();

        if ($openingTime) {
            $this->minTime = $openingTime['start'];
            $this->maxTime = $openingTime['end'];
        }

        $minSplitted = Str::of($this->minTime)->explode(':');
        $maxSplitted = Str::of($this->maxTime)->explode(':');

        $this->min = intval($minSplitted->first());

        $max = $maxSplitted->first();
        if ($maxSplitted->last() == '00') {
            $max--;
        }

        $this->max = $max;
    }

    public function render()
    {
        $query = Schedule::query()
            ->where('date', $this->date);

        if ($this->model) {
            $query->whereIn('user_id', function ($query) {
                if (class_basename($this->model) == 'Group') {
                    $query->select('user_id')
                        ->from('group_user')
                        ->where('group_user.group_id', $this->model->id);
                } else {
                    $query->select('id')
                        ->from('users')
                        ->where('organization_id', $this->model->id);
                }
            });
        }

        $schedules = $query->select(
            DB::raw('count(*) as total'),
            'date',
            'start',
            'end'
        )
            ->groupBy('start', 'end', 'date')
            ->orderBy('start')
            ->orderBy('end')
            ->get();

        $scheduleDataList = $this->createScheduleDataList($schedules);

        $formattedDate = Carbon::parse($this->date)->format(config('setting.format.date'));

        return view('livewire.schedules.index', [
            'schedules' => $scheduleDataList,
            'formattedDate' => $formattedDate,
        ]);
    }

    private function createScheduleDataList($schedules)
    {
        $scheduleDataList = [];

        for ($x = $this->min; $x <= $this->max; $x++) {
            $startTime = $x;
            $endTime = $x + 1;

            if ($startTime < 10) {
                $startTime = "0$startTime:00:00";
            } else {
                $startTime = "$startTime:00:00";
            }

            if ($endTime < 10) {
                $endTime = "0$endTime:00:00";
            } else {
                $endTime = "$endTime:00:00";
            }

            $start = Carbon::parse($this->date . ' ' . $startTime);
            $end = Carbon::parse($this->date . ' ' . $endTime);

            $dataForThisHour = [
                'start' => $start->format('H:i'),
                'end' => $end->format('H:i'),
                'total' => 0,
            ];

            foreach ($schedules as $item) {
                $itemStart = Carbon::parse($item->date . ' ' . $item->start);
                $itemEnd = Carbon::parse($item->date . ' ' . $item->end);

                if (
                    $start->between($itemStart, $itemEnd->copy()->subSecond(5))
                    || $end->between($itemStart->copy()->addSecond(5), $itemEnd)
                ) {
                    $dataForThisHour['total'] = $dataForThisHour['total'] + $item->total;
                }
            }

            $scheduleDataList[] = $dataForThisHour;
        }

        return collect($scheduleDataList);
    }
}
