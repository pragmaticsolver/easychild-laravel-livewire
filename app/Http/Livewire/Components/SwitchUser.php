<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class SwitchUser extends Component
{
    public $enabled;
    public $child;
    public $switchVisible;

    public function mount()
    {
        $this->enabled = false;
        $this->switchVisible = false;

        if ($this->isImpersonating) {
            $child = $this->parent->parent_current_child;

            if ($child) {
                $this->child = $child->id;
            }
        }

        if ($this->isImpersonating && $this->parent) {
            $this->switchVisible = true;
        }
    }

    public function manager()
    {
        return app('impersonate');
    }

    public function getIsImpersonatingProperty()
    {
        return $this->manager()->isImpersonating();
    }

    public function getParentProperty()
    {
        return $this->manager()->getImpersonator();
    }

    public function switchChild()
    {
        if (auth()->user()->id != $this->child && $this->parent->isParent()) {
            $childrens = $this->parent->childrens;

            if ($child = $childrens->where('id', $this->child)->first()) {
                $this->parent->setParentCurrentChild($child->id);
                $this->manager()->take($this->parent, $child);

                session()->flash('success', trans('users.parent.switched-to', ['name' => $child->full_name]));

                return redirect(route('dashboard'));
            }
        }
    }

    public function render()
    {
        $childrens = collect([]);

        if ($this->isImpersonating && $this->parent) {
            $childrens = $this->parent->childrens;
        }

        return view('livewire.components.switch-user', compact('childrens'));
    }
}
