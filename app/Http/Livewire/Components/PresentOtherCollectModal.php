<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use App\Models\User;

class PresentOtherCollectModal extends Component
{
    public $name;
    public $showModal = false;
    public $title = '';

    protected $rules = [
        'name' => 'required|min:6',
    ];

    protected $listeners = [
        'present-show-other-collect-modal' => 'open'
    ];

    public function open($params)
    {
        $this->title = trans('dashboard.present_other_collection_modal_title', ['name' => $params['user_name']]);
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
    }

    public function submit()
    {
        $this->validate();
        $this->showModal = false;
        // Execution doesn't reach here if validation fails.

        $this->emitTo('components.present-modal', 'present-other-collect-log', [
            'contact_name' => $this->name
        ]);
    }

    public function render()
    {
        return view('livewire.components.present-other-collect-modal');
    }
}
