<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class FileModal extends Component
{
    protected $listeners = [
        'file-model-open' => 'open',
    ];

    public $showModal = false;
    public $file;

    public function open($file)
    {
        $this->file = $file;

        $this->showModal = true;
    }

    public function close()
    {
        $this->file = null;
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.components.file-modal');
    }
}
