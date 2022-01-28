<?php

namespace App\Http\Livewire\Users\Edit;

use App\Http\Livewire\Component;
use App\Models\Contact;
use App\Models\User;
use App\Traits\HasAvatarFileUploader;

class Contacts extends Component
{
    use HasAvatarFileUploader;

    public Contact $contact;
    public User $user;
    public $showModal = false;

    public $newAvatar;
    public $avatar;

    public function mount(User $user)
    {
        $this->user = $user;
        $this->resetContactItem();
    }

    public function rules()
    {
        return [
            'contact.name' => 'required',
            'contact.address' => 'nullable',
            'contact.landline' => 'nullable',
            'contact.mobile' => 'nullable',
            'contact.job' => 'nullable',
            'contact.relationship' => 'nullable',
            'contact.notes' => 'nullable',
            'contact.legal' => 'required|boolean',
            'contact.can_collect' => 'required|boolean',
            'contact.emergency_contact' => 'required|boolean',
        ];
    }

    private function resetContactItem()
    {
        $this->contact = Contact::make([
            'legal' => false,
            'can_collect' => false,
            'emergency_contact' => false,
            'user_id' => $this->user->id,
        ]);
    }

    public function edit(Contact $contact)
    {
        $this->authorize('update', $contact);

        $this->contact = $contact;

        $this->avatar = $contact->avatar_url;
        $this->newAvatar = null;
        $this->showModal = true;
    }

    public function submit()
    {
        $msg = trans('contacts.create_success_msg');

        if ($this->contact->getKey()) {
            $msg = trans('contacts.update_success_msg');
        }

        $this->contact->user_id = $this->user->id;

        $this->contact->avatar = $this->uploadImage($this->newAvatar, $this->contact, 'avatar');
        $this->contact->save();

        $this->showModal = false;

        $this->resetContactItem();

        $this->emitMessage('success', $msg);
    }

    public function createNew()
    {
        $this->resetContactItem();

        $this->showModal = true;
    }

    public function deleteContact(Contact $contact)
    {
        $this->authorize('delete', $contact);

        $contact->delete();

        $this->resetContactItem();

        $this->emitMessage('success', trans('contacts.delete_success_msg'));
    }

    public function render()
    {
        $contacts = $this->user->contacts()
            ->orderBy('id')
            ->get();

        return view('livewire.users.edit.contacts', compact('contacts'));
    }
}
