<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class UserOrgBlock extends Component
{
    protected $listeners = [
        'parent.child-switched' => '$refresh',
    ];

    public function render()
    {
        $child = auth()->user()->parent_current_child;

        $organization = $child->organization;
        $schedule = $child->schedules()
            ->where('date', now()->format('Y-m-d'))
            ->first();

        return view('livewire.components.user-org-block', compact('organization', 'schedule'));
    }
}
