<?php

namespace App\Http\Livewire\Components;

use App\Models\Contact;
use App\Models\User;
use App\Http\Livewire\Component;

class PresentModal extends Component
{
    public $showModal = false;
    public $contacts = [];
    public $user = null;
    public $user_name = '';
    public $title = '';
    public $schedule_uuid = '';
    public $from_action = '';

    protected $listeners = [
        'present-show-modal' => 'open',
        'present-other-collect-log' => 'otherLogCollection'
    ];

    public function open($params)
    {
        $this->title = trans('dashboard.present_modal_title', ['name' => $params['user_name']]);
        $user = User::findByUuid($params['user_uuid']);
        $this->from_action = $params['from_action'];

        $this->authorize('update', $user);

        $this->user_name = $params['user_name'];
        $this->user = $user;
        $this->schedule_uuid = $params['uuid'];
        $contacts = Contact::where('user_id', '=', $user->id)->where('can_collect', '=', 1)->get();
        $this->contacts = $contacts;
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function showOtherModal()
    {
        $this->emitTo('components.present-other-collect-modal', 'present-show-other-collect-modal', [
            'user_name' => $this->user_name
        ]);
    }

    public function otherLogCollection($params)
    {
        $this->authorize('update', $this->user);
        $contact_name = $params['contact_name'];
        $this->emitLog($contact_name);
    }

    public function logCollection($contact_name)
    {
        $this->showModal = false;
        $this->emitLog($contact_name);
    }

    private function emitLog($contact_name)
    {
        $this->showModal = false;

        if($this->from_action == 'dashboard') {
            $this->emitTo('components.principal-time-board', 'principal-presence-end', [
                'schedule_uuid' => $this->schedule_uuid,
                'contact_name' => $contact_name,
                'user_id' => $this->user->id
            ]);
        } elseif ($this->from_action == 'presence') {
            $this->emitTo('group-class.index', 'presence-end', [
                'contact_name' => $contact_name,
                'schedule_uuid' => $this->schedule_uuid,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.components.present-modal');
    }
}
