<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class Calendar extends Component
{
    public $month;
    public $monthName;

    public $year;

    public $current;

    public $weekList;
    private $weekStartsAt = 1;

    public function mount()
    {
        $now = now();

        $this->current = $now;
        $this->month = $now->month;
        $this->monthName = $now->monthName;

        $this->year = $now->format('y');

        // $this->weekStartsAt = $weekStartsAt;
        $this->setWeekList();
    }

    private function setWeekList()
    {
        $now = now()->startOfWeek($this->weekStartsAt);

        $list = [];

        for ($i = 0; $i <= 6; $i++) {
            $list[] = $now->copy()->addDays($i)->minDayName;
        }

        $this->weekList = $list;
    }

    public function next()
    {
        $this->current->addMonth();
        $this->month = $this->current->month;
        $this->monthName = $this->current->monthName;

        $this->year = $this->current->format('y');
    }

    public function previous()
    {
        $this->current->subMonth();
        $this->month = $this->current->month;
        $this->monthName = $this->current->monthName;

        $this->year = $this->current->format('y');
    }

    public function getDaysListProperty()
    {
        $list = [];

        $startOfMonth = $this->current->copy()->startOfMonth()->startOfWeek();
        $endOfMonth = $this->current->copy()->endOfMonth()->endOfWeek();

        $currentDay = $startOfMonth->copy();
        do {
            $list[] = [
                'currentMonth' => $currentDay->month === $this->current->month,
                'isCurrent' => $currentDay->isSameDay(now()),
                'text' => $currentDay->day,
            ];

            $currentDay = $currentDay->addDay();
        } while ($currentDay < $endOfMonth);

        return $list;
    }

    public function render()
    {
        return view('livewire.components.calendar');
    }
}
