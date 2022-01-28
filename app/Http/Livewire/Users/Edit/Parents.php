<?php

namespace App\Http\Livewire\Users\Edit;

use App\Actions\User\AssignParentToChildAction;
use App\Actions\User\RemoveParentFromChild;
use App\Http\Livewire\Component;
use App\Models\ParentLink;
use App\Models\User;
use App\Rules\ParentChildLinkRule;

class Parents extends Component
{
    public User $user;
    public $email = '';
    public $showModal;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->resetModel();

        $this->showModal = false;
    }

    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                new ParentChildLinkRule($this->user),
            ],
        ];
    }

    public function submit()
    {
        $this->authorize('update', ParentLink::make([
            'child_id' => $this->user->id,
        ]));

        $messages = [
            'new' => trans('parents.new_create_success_msg'),
            'linked' => trans('parents.parent_linked_success_msg'),
        ];

        if (! $parent = User::where('email', $this->email)->first()) {
            $parent = User::make([
                'email' => $this->email,
                'role' => 'Parent',
            ]);
        }

        $status = AssignParentToChildAction::run($this->user, $parent);

        $this->resetModel();
        $this->showModal = false;

        $this->emitMessage('success', $messages[$status]);
    }

    public function createNew()
    {
        $this->resetModel();

        $this->showModal = true;
    }

    private function resetModel()
    {
        $this->email = '';
    }

    public function resendLinkEmail(ParentLink $parentLink)
    {
        $this->authorize('update', $parentLink);

        $parent = User::query()
            ->where('email', $parentLink->email)
            ->first();

        if ($parent && ! $parentLink->linked) {
            AssignParentToChildAction::run($this->user, $parent);

            return $this->emitMessage('success', trans('parents.resend_link_email_success_msg'));
        }

        $this->emitMessage('error', trans('parents.resend_link_email_error_msg'));
    }

    public function unlinkParent(ParentLink $parentLink)
    {
        $this->authorize('delete', $parentLink);

        RemoveParentFromChild::run($parentLink);

        $this->emitMessage('success', trans('parents.parent_unlinked_success_msg'));
    }

    public function render()
    {
        $parents = ParentLink::query()
            ->where('child_id', $this->user->id)
            ->get();

        return view('livewire.users.edit.parents', compact('parents'));
    }
}
