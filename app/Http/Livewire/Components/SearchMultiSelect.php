<?php

namespace App\Http\Livewire\Components;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Arr;
use Livewire\Component;

class SearchMultiSelect extends Component
{
    public $selected;
    public $displayKey;
    public $emitUpWhenUpdated;
    public $listenToEmit;
    public $dropVisible;
    public $extraLimitor;
    public $targetModel;
    public $search;
    public $modelIdKey;
    public $enableSearch;
    public $orderBy;
    public $wireKey;

    public function mount(
        $targetModel,
        $selected,
        $displayKey,
        $orderBy,
        $emitUpWhenUpdated = null,
        $listenToEmit = null,
        $enableSearch = true,
        $wireKey = 'wire-key',
        $extraLimitor = [],
        $modelIdKey = 'id'
    ) {
        $this->wireKey = $wireKey;
        $this->orderBy = $orderBy;
        $this->targetModel = $targetModel;
        $this->enableSearch = $enableSearch;
        $this->modelIdKey = $modelIdKey;
        $this->selected = $selected;
        $this->displayKey = $displayKey;
        $this->emitUpWhenUpdated = $emitUpWhenUpdated;
        $this->listenToEmit = $listenToEmit;
        $this->extraLimitor = $extraLimitor;

        $this->dropVisible = false;
    }

    public function updatedTargetModel($value)
    {
        if (! in_array($value, ['user', 'group'])) {
            return abort(404);
        }
    }

    public function getSelectedItemsQueryProperty()
    {
        $model = User::class;

        if ($this->targetModel == 'group') {
            $model = Group::class;
        }

        $query = $model::query();

        if (! empty($this->selected)) {
            $query->whereIn($this->modelIdKey, $this->selected);
        } else {
            return collect();
        }

        return $query->get();
    }

    public function getAvailableItemsQueryProperty()
    {
        $model = User::class;

        if ($this->targetModel == 'group') {
            $model = Group::class;
        }

        $query = $model::query()
            ->when($this->search, function ($query) {
                $query->search($this->search);
            });

        if (! auth()->user()->isAdmin()) {
            $query->where('organization_id', auth()->user()->organization_id);
        }

        if (! empty($this->extraLimitor)) {
            foreach ($this->extraLimitor as $key => $value) {
                if ($key == 'whereNotIn') {
                    $query->whereNotIn($value[0], $value[1]);
                } else {
                    if (gettype($value) == 'array') {
                        $query->whereIn($key, $value);
                    } else {
                        $query->where($key, $value);
                    }
                }
            }
        }

        if ($this->selected && ! empty($this->selected)) {
            $query->whereNotIn($this->modelIdKey, $this->selected);
        }

        if ($this->orderBy) {
            $query->orderBy($this->orderBy);
        }

        return $query->limit(20)->get();
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

    public function addSelect($modelIdKey)
    {
        $selected = $this->selected;
        $this->selected = collect($selected)->add($modelIdKey)->unique()->values()->all();

        $this->dropVisible = false;
        $this->search = '';

        $this->dispatchEvent();
    }

    public function removeSelect($modelIdKey)
    {
        $this->selected = collect($this->selected)->reject(function ($value) use ($modelIdKey) {
            return $value == $modelIdKey;
        })->values()->all();

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

    public function render()
    {
        $availableItems = $this->availableItemsQuery;
        $selectedItems = $this->selectedItemsQuery;

        return view('livewire.components.search-multi-select', compact('availableItems', 'selectedItems'));
    }
}
