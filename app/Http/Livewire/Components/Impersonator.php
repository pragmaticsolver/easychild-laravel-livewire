<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Component;
use App\Models\Organization;
use App\Models\User;

class Impersonator extends Component
{
    public function impersonate(User $user)
    {
        if ($user->canBeImpersonated()) {
            auth()->user()->impersonate($user);

            session()->flash('success', trans('impersonations.join_success', ['name' => $user->full_name]));

            return redirect()->route('dashboard');
        }

        $this->emitMessage('error', trans('impersonations.join_error'));
    }

    public function impersonateOrganization(Organization $organization)
    {
        $user = auth()->user();

        if (! $user->isContractor()) {
            $this->emitMessage('error', trans('impersonations.join_error'));

            return;
        }

        $firstManager = $organization->users()
            ->where('role', 'Manager')
            ->first();

        if ($firstManager) {
            return $this->impersonate($firstManager);
        }

        $this->emitMessage('error', trans('impersonations.join_error'));
    }

    public function clearImpersonate()
    {
        auth()->user()->leaveImpersonation();

        session()->flash('success', trans('impersonations.join_success', ['name' => auth()->user()->full_name]));

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.components.impersonator');
    }
}
