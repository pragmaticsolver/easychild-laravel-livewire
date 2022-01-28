<?php

namespace App\Http\Livewire\Organizations;

use App\Http\Livewire\Component;
use App\Models\Organization;
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
        'name',
        'address',
        'groups_count',
        'users_count',
    ];

    public $colFilters = [
        'organization_name' => [
            'filter' => null,
            'query' => null,
        ],
        'organization_address' => [
            'filter' => null,
            'query' => null,
        ],
        'organization_groups' => [
            'filter' => null,
            'query' => null,
        ],
        'organization_users' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'organization_name' => [
            'key' => 'organizations.name',
            'normal' => true,
        ],
        'organization_address' => [
            'key' => 'organizations.address',
            'normal' => true,
        ],
        'organization_groups' => [
            'key' => 'groups_count',
            'normal' => false,
        ],
        'organization_users' => [
            'key' => 'users_count',
            'normal' => false,
        ],
    ];

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function download()
    {
        $orgs = $this->mainQuery->get();

        if (! $orgs->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/" . auth()->user()->uuid . '.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid . '.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($orgs as $org) {
            $writer->addRow([
                trans('excel.organizations.id') => $org->id,
                trans('excel.organizations.name') => $org->name,
                trans('excel.organizations.address') => $org->address,
                trans('excel.organizations.groups') => $org->groups_count,
                trans('excel.organizations.users') => $org->users_count,
                trans('excel.organizations.created_at') => $org->created_at->format(config('setting.format.date')),
            ]);
        }

        $fileName = 'organizations-' . now()->format(config('setting.format.date')) . '.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    public function mount()
    {
        $this->authorize('viewAny', Organization::class);

        if (! $this->sortBy) {
            $this->sortBy = $this->sortableCols[0];
        }

        $this->placeholder = trans('organizations.search');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getMainQueryProperty()
    {
        $query = Organization::query();

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

        $query->withCount([
            'users',
            'groups',
        ]);

        foreach ($extraFilters as $col) {
            $query->having($col['col'], $col['operator'], $col['query']);
        }

        return $query;
    }

    public function render()
    {
        $organizations = $this->mainQuery->paginate(config('setting.perPage'));

        return view('livewire.organizations.index', compact('organizations'));
    }
}
