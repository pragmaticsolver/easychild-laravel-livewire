<?php

namespace App\Http\Livewire\OpeningTimes;

use App\Http\Livewire\Component;

class Index extends Component
{
    public function render()
    {
        $openingTimes = auth()->user()->organization->settings['opening_times'];

        return view('livewire.opening-times.index', compact('openingTimes'));
    }
}
