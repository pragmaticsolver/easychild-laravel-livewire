<?php

namespace App\Http\Livewire;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component as LivewireComponent;

class Component extends LivewireComponent
{
    use AuthorizesRequests;

    public function emitMessage($type, $message)
    {
        $this->emit('server-message', [
            'type' => $type,
            'message' => $message,
        ]);
    }

    public function updateValueByKey($data)
    {
        $key = $data['key'];

        $this->$key = $data['value'];

        $methodKey = Str::camel('on_' . $key . '_updated');

        if (method_exists($this, $methodKey)) {
            $this->$methodKey($data['value'], Arr::has($data, 'encrypted'));
        }
    }

    protected function getValueByKey($arr, $key, $default = true)
    {
        if (Arr::has($arr, $key)) {
            return $arr[$key];
        }

        return $default;
    }

    protected function getNextOrPrevWeekday($now = null, $wants = null)
    {
        if (! $now) {
            $now = now();
        }

        if (! $wants) {
            if ($now->isWeekday()) {
                return $now;
            }
        } else {
            if ($wants == 'prev') {
                $now->subDay();
            } else {
                $now->addDay();
            }
        }

        while (! $now->isWeekday()) {
            if ($wants == 'prev') {
                $now->subDay();
            } else {
                $now->addDay();
            }
        }

        return $now;
    }
}
