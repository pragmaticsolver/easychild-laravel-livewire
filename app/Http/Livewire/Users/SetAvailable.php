<?php

namespace App\Http\Livewire\Users;

use App\Http\Livewire\Component;
use App\Models\User;

class SetAvailable extends Component
{
    public $available;
    public $userUuid;

    public function mount($user){
        $this->available = $user->photo_permission;
        $this->userUuid = $user->uuid;
    }

    public function updated($name, $value)
    {
        if($this->userUuid) {
            User::where('uuid', $this->userUuid)
                ->update(['photo_permission' => $value]);
        }
        $this->emitMessage('success', trans('users.update_photo_permission_success', ['value' => $value ? trans('extras.photo_permission_granted') : trans('extras.photo_permission_disabled')]));
    }

    public function render()
    {
        return view('livewire.users.set-available');
    }
}