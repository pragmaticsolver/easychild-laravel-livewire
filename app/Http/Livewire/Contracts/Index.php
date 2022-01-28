<?php

namespace App\Http\Livewire\Contracts;

use App\Http\Livewire\Component;
use App\Models\Contract;
use App\Rules\BetweenOpeningTimeRangeRule;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Index extends Component
{
    use WithPagination, HasSorting, HasIndexPageFilters;

    public $placeholder;
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => ''],
        'sortOrder' => ['except' => 'ASC'],
        'page',
    ];

    protected $sortableCols = [
        'title',
        'time_per_day',
        'overtime',
    ];

    public $colFilters = [
        'contract_title' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'contract_title' => [
            'key' => 'contracts.title',
            'normal' => true,
        ],
    ];

    public $showModal = false;
    public Contract $contract;

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function mount()
    {
        if (! $this->sortBy) {
            $this->sortBy = $this->sortableCols[0];
        }

        $this->contract = Contract::make();

        $this->placeholder = trans('contracts.search');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'contract.title' => 'required',
            'contract.time_per_day' => 'required',
            'contract.bring_until' => ['nullable', new BetweenOpeningTimeRangeRule],
            'contract.collect_until' => ['nullable', new BetweenOpeningTimeRangeRule],
            'contract.overtime' => 'nullable',
        ];
    }

    public function edit($uuid)
    {
        $contract = Contract::findByUUIDOrFail($uuid);
        $this->authorize('update', $contract);

        $this->contract = $contract;
        $this->showModal = true;
    }

    public function delete($uuid)
    {
        $contract = Contract::findByUUIDOrFail($uuid);
        $this->authorize('delete', $contract);

        $contract->delete();

        $this->contract = Contract::make();

        $this->emitMessage('success', trans('contracts.delete_success_msg'));
    }

    public function submit()
    {
        $this->validate();

        $msg = trans('contracts.create_success_msg');

        if ($this->contract->getKey()) {
            $msg = trans('contracts.update_success_msg');
        }

        $this->contract->organization_id = auth()->user()->organization_id;

        if (! $this->contract->overtime) {
            $this->contract->overtime = 0;
        }

        $this->contract->save();
        $this->showModal = false;

        $this->contract = Contract::make();

        $this->emitMessage('success', $msg);
    }

    public function createNew()
    {
        if ($this->contract->getKey()) {
            $this->contract = Contract::make();
        }

        $this->showModal = true;
    }

    public function download()
    {
        $contracts = $this->mainQuery->get();

        if (! $contracts->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/" . auth()->user()->uuid . '.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid . '.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($contracts as $contract) {
            $writer->addRow([
                trans('excel.contracts.id') => $contract->id,
                trans('excel.contracts.title') => $contract->title,
                trans('excel.contracts.time_per_day_hr') => $contract->time_per_day,
                trans('excel.contracts.overtime_day_min') => $contract->overtime,
                trans('excel.contracts.bring_until') => $contract->bring_until ?? '-',
                trans('excel.contracts.collect_until') => $contract->collect_until ?? '-',
            ]);
        }

        $fileName = 'contracts-' . now()->format(config('setting.format.date')) . '.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    public function getMinMaxTimeProperty()
    {
        $day = now();

        while ($day->isWeekend()) {
            $day->addDay();
        }

        $openingTimes = auth()->user()->organization->settings['opening_times'];

        $currentWeekDay = $day->dayOfWeek - 1;

        $openingTime = collect($openingTimes)->where('key', $currentWeekDay)->first();

        $min = '00:00';
        $max = '23:30';

        if ($openingTime) {
            $min = $openingTime['start'];
            $max = $openingTime['end'];
        }

        return [
            'min' => $min,
            'max' => $max,
        ];
    }

    public function getMainQueryProperty()
    {
        $query = Contract::query()
            ->where('organization_id', auth()->user()->organization_id);

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->sortBy) {
            $query->sort($this->sortBy, $this->sortOrder);
        }

        $colFilters = collect($this->modelFilter);
        $normalFilters = $colFilters->where('normal', true)->all();
        $extraFilters = $colFilters->where('normal', false)->all();

        if (count($normalFilters)) {
            $this->filterScope($query, $normalFilters);
        }

        if (count($normalFilters)) {
            foreach ($extraFilters as $col) {
                $query->having($col['col'], $col['operator'], $col['query']);
            }
        }

        return $query;
    }

    public function render()
    {
        $contracts = $this->mainQuery->paginate(config('setting.perPage'));

        return view('livewire.contracts.index', compact('contracts'));
    }
}
