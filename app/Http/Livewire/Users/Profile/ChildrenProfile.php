<?php

namespace App\Http\Livewire\Users\Profile;

use App\Models\User;
use Livewire\Component;

class ChildrenProfile extends Component
{
    public User $parent;

    public function mount()
    {
        $this->parent = auth()->user();
    }

    public function render()
    {
        $children = $this->parent->childrens;

        return view('livewire.users.profile.children-profile', compact('children'));
    }
}
