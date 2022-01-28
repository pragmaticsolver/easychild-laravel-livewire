<?php

namespace App\Http\Livewire\Components;

use App\Http\Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SearchSelect extends Component
{
    public $search = '';
    public $provider;
    public $emitUpWhenUpdated;
    public $selected = null;
    public $selectedData = null;
    public $emitWhenUpdated;
    public $listenToEmit;
    public $selectOpen;

    public function mount(
        $provider,
        $selected = null,
        $emitUpWhenUpdated = null,
        $emitWhenUpdated = false,
        $listenToEmit = false
    ) {
        $this->provider = $provider;
        $this->emitUpWhenUpdated = $emitUpWhenUpdated;
        $this->selected = $selected;
        $this->emitWhenUpdated = $emitWhenUpdated;
        $this->listenToEmit = $listenToEmit;
        $this->selectOpen = false;

        if ($this->selected) {
            $this->setDefaultValue();
        }
    }

    private function setDefaultValue()
    {
        $provider = $this->provider;
        $model = 'App\\Models\\' . Str::studly($provider['model']);

        $selectedQuery = $model::where($provider['key'], $this->selected);
        $selectedQuery->withLimitOption($provider);
        $selected = $selectedQuery->first();

        if ($selected) {
            $text = $provider['text'];
            $this->selectedData = $selected->$text;
        }
    }

    public function changeSearch($value)
    {
        $this->search = $value;
    }

    public function getListeners()
    {
        $provider = $this->provider;
        $listeners = [];

        if (Arr::has($provider, 'updateOn')) {
            $listeners[$provider['updateOn']] = 'onUpdatingDependentValue';
        }

        if ($this->listenToEmit) {
            $listenToEmit = Arr::wrap($this->listenToEmit);

            foreach ($listenToEmit as $item) {
                $listeners[$item] = 'updateSelectedItem';
            }
        }

        return $listeners;
    }

    public function updateSelectedItem($id)
    {
        $this->selected = $id;
    }

    public function selectItem($id)
    {
        $this->selected = $id;

        $this->notifyValueChanged();

        $this->selectOpen = false;
    }

    public function clearItem()
    {
        $this->selected = null;
        $this->selectedData = null;

        $this->notifyValueChanged();
    }

    public function notifyValueChanged()
    {
        if ($this->emitUpWhenUpdated) {
            $this->emitUp('updateValueByKey', [
                'key' => $this->emitUpWhenUpdated,
                'value' => $this->selected,
            ]);
        }

        if ($this->emitWhenUpdated && is_array($this->emitWhenUpdated)) {
            foreach ($this->emitWhenUpdated as $item) {
                $this->emitTo($item[0], $item[1], $this->selected);
            }
        }
    }

    public function onUpdatingDependentValue($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $this->provider[$key] = $value;
            }
        } else {
            $this->provider['limitValue'] = $data;
        }
    }

    public function render()
    {
        $data = collect([]);

        $provider = $this->provider;

        $model = 'App\\Models\\' . Str::studly($provider['model']);
        $pluralModel = Str::plural($provider['model']);

        $query = $model::query();
        $query->withLimitOption($provider);
        $query->search($this->search);

        $data = $query->limit(config('setting.perPage'))
            ->get();

        $data = collect($data->toArray());

        if ($this->selected) {
            $selectedQuery = $model::where($provider['key'], $this->selected);

            $selectedQuery->withLimitOption($provider);
            $selected = $selectedQuery->first();

            if ($selected) {
                $text = $provider['text'];
                $this->selectedData = $selected->$text;

                $data->merge(collect($selected));
            } else {
                $this->selected = null;
                $this->selectedData = null;
            }
        } else {
            $this->selectedData = null;
        }

        if (Arr::has($provider, 'orderBy') && $provider['orderBy']) {
            $data = $data->sortBy($provider['orderBy'])->values();
        }

        return view('livewire.components.search-select', compact('data'));
    }
}
