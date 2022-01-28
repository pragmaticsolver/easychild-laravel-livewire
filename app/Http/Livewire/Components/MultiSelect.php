<?php

namespace App\Http\Livewire\Components;

use Illuminate\Support\Arr;
use App\Http\Livewire\Component;

class MultiSelect extends Component
{
    public $items;
    public $selected;
    public $displayKey;
    public $emitUpWhenUpdated;
    public $listenToEmit;
    public $dropVisible;

    public function mount(
        $items,
        $selected,
        $displayKey,
        $emitUpWhenUpdated = null,
        $listenToEmit = null
    ) {
        $this->items = $items;
        $this->selected = $selected;
        $this->displayKey = $displayKey;
        $this->emitUpWhenUpdated = $emitUpWhenUpdated;
        $this->listenToEmit = $listenToEmit;

        $this->dropVisible = false;
    }

    public function getListeners()
    {
        $listeners = [];

        if ($this->listenToEmit) {
            $items = Arr::wrap($this->listenToEmit);

            foreach ($items as $item) {
                $listeners[$item] = 'onUpdatingKeyValuePairs';
            }
        }

        return $listeners;
    }

    public function onUpdatingKeyValuePairs($data)
    {
        $key = $data['key'];
        $this->$key = $data['value'];
    }

    public function addSelect($uuid)
    {
        list($items, $selected) = $this->transferItemFromOneToAnother($this->items, $this->selected, $uuid);
        $this->items = $items;
        $this->selected = $selected;

        $this->dropVisible = false;

        $this->dispatchEvent();
    }

    public function removeSelect($uuid)
    {
        list($selected, $items) = $this->transferItemFromOneToAnother($this->selected, $this->items, $uuid);
        $this->items = $items;
        $this->selected = $selected;

        $this->dropVisible = false;

        $this->dispatchEvent();
    }

    private function dispatchEvent()
    {
        if ($this->emitUpWhenUpdated) {
            $items = Arr::wrap($this->emitUpWhenUpdated);

            foreach ($items as $key => $value) {
                $this->emitUp('updateValueByKey', [
                    'key' => $key,
                    'value' => $this->$value,
                ]);
            }
        }
    }

    private function transferItemFromOneToAnother($from, $to, $uuid)
    {
        $items = collect($from);
        $selected = collect($to);

        $isAvailable = $items->where('uuid', $uuid)->first();
        $alreadyPresent = $selected->where('uuid', $uuid)->first();

        if ($isAvailable && ! $alreadyPresent) {
            $selected->push($isAvailable);
            $items = $items->where('uuid', '!=', $uuid);
        }

        $from = $items->all();
        $to = $selected->all();

        return [
            $from,
            $to,
        ];
    }

    public function render()
    {
        return view('livewire.components.multi-select');
    }
}
