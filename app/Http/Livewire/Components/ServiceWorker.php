<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class ServiceWorker extends Component
{
    public function updateSubscription($data)
    {
        if (auth()->user()->isImpersonated()) {
            return;
        }

        auth()->user()->updatePushSubscription(
            $data['endpoint'],
            $data['publicKey'],
            $data['authToken'],
            $data['contentEncoding'],
        );
    }

    public function removeSubscription($data)
    {
        if (auth()->user()->isImpersonated()) {
            return;
        }

        auth()->user()->deletePushSubscription($data['endpoint']);
    }

    public function render()
    {
        return view('livewire.components.service-worker');
    }
}
