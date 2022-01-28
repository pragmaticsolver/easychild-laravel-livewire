<?php

namespace App\Http\Livewire\Informations;

use App\Models\Information;
use Livewire\Component;

class Nav extends Component
{
    protected $listeners = ['nav.informations.update' => '$refresh'];

    public function render()
    {
        $user = auth()->user();

        $query = Information::query()
            ->where('organization_id', $user->organization_id);

        if (! $user->isManager()) {
            $query->whereJsonContains('informations.roles', $user->role);
        }

        $number = $query->withLastNotification()
            ->where('informations.creator_id', '!=', $user->id)
            ->get()
            ->where('last_notification', null)
            ->count();

        return view('livewire.informations.nav', compact('number'));
    }
}
