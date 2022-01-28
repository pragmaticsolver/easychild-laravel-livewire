<?php

namespace App\Http\Livewire\OpeningTimes;

use App\Http\Livewire\Component;
use Carbon\Carbon;

class Item extends Component
{
    public $key;
    public $start;
    public $end;

    public function mount($openingTime)
    {
        $this->key = $openingTime['key'];
        $this->start = $openingTime['start'];
        $this->end = $openingTime['end'];
    }

    public function updated($name, $value)
    {
        if (in_array($name, ['start', 'end'])) {
            $this->saveOpeningTime();
        }
    }

    public function saveOpeningTime()
    {
        if (! $this->start) {
            return $this->emitMessage('error', trans('openingtimes.start_required'));
        }

        if (! $this->end) {
            return $this->emitMessage('error', trans('openingtimes.end_required'));
        }

        $start = Carbon::parse($this->start);
        $end = Carbon::parse($this->end);

        if ($start >= $end) {
            return $this->emitMessage('error', trans('openingtimes.start_less_than_end'));
        }

        $this->saveToDatabase();

        session()->flash('success', trans('openingtimes.create_success', ['day' => $this->getLongWeekDayName()]));

        $this->emitMessage('success', trans('openingtimes.create_success', ['day' => $this->getLongWeekDayName()]));
    }

    private function saveToDatabase()
    {
        $organization = auth()->user()->organization;
        $settings = $organization->settings;

        $openingTimes = $settings['opening_times'];

        $data = [];
        foreach ($openingTimes as $item) {
            $newItem = $item;

            if ($item['key'] === $this->key) {
                $newItem['start'] = $this->start;
                $newItem['end'] = $this->end;
            }

            $data[] = $newItem;
        }

        $settings['opening_times'] = $data;
        $organization->settings = $settings;
        $organization->save();
    }

    private function getLongWeekDayName()
    {
        return now()->startOfWeek()
            ->addDays($this->key)
            ->dayName;
    }

    public function getShortWeekDayNameProperty()
    {
        return now()->startOfWeek()
            ->addDays($this->key)
            ->shortDayName;
    }

    public function render()
    {
        return view('livewire.opening-times.item');
    }
}
