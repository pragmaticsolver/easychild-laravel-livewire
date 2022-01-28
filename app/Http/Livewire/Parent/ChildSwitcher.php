<?php

namespace App\Http\Livewire\Parent;

use App\Http\Livewire\Component;
use App\Models\User;
use Illuminate\Http\Response;

class ChildSwitcher extends Component
{
    public User $parent;
    public User $current;

    public function mount()
    {
        $authUser = auth()->user();

        if (! $authUser) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if (! $authUser->isParent()) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $this->parent = $authUser;
        $this->current = $this->parent->parent_current_child;
    }

    public function switchChild(User $child)
    {
        if ($child->uuid == $this->current->uuid) {
            return;
        }

        $childrens = $this->parent->childrens;

        if (! $childrens->contains($child)) {
            return;
        }

        $this->parent->setParentCurrentChild($child->id);
        $this->current = $child;

        // Send child changed event
        $this->emit('parent.child-switched', $child->id);
    }

    public function render()
    {
        $children = $this->parent->childrens;

        return view('livewire.parent.child-switcher', compact('children'));
    }
}
