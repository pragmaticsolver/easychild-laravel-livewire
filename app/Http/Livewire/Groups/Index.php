<?php

namespace App\Http\Livewire\Groups;

use App\Http\Livewire\Component;
use App\Models\Conversation;
use App\Models\ConversationNotification;
use App\Models\Group;
use App\Models\Message;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Illuminate\Support\Facades\DB;
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
        'principals_count',
        'users_count',
    ];

    public $colFilters = [
        'group_name' => [
            'filter' => null,
            'query' => null,
        ],
        'users_count' => [
            'filter' => null,
            'query' => null,
        ],
        'principals_count' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'group_name' => [
            'key' => 'groups.name',
            'normal' => true,
        ],
        'users_count' => [
            'key' => 'users_count',
            'normal' => false,
        ],
        'principals_count' => [
            'key' => 'principals_count',
            'normal' => false,
        ],
    ];

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function mount()
    {
        $this->authorize('viewAny', Group::class);

        if (! $this->sortBy) {
            $this->sortBy = $this->sortableCols[0];
        }

        $this->placeholder = trans('groups.search');
    }

    public function download()
    {
        $groups = $this->mainQuery
            ->addSelect([
                'organization_name' => DB::table('organizations')
                    ->whereColumn('organizations.id', 'groups.organization_id')
                    ->select('name')
                    ->limit(1),
            ])->get();

        if (! $groups->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/" . auth()->user()->uuid . '.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid . '.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($groups as $group) {
            $row = [
                trans('excel.groups.id') => $group->id,
                trans('excel.groups.name') => $group->name,
                trans('excel.groups.principals') => $group->principals_count,
                trans('excel.groups.users') => $group->users_count,
            ];

            if (auth()->user()->isAdmin()) {
                $row[trans('excel.groups.organization')] = $group->organization_name . ' - (' . $group->organization_id . ')';
            }

            $row[trans('excel.groups.created_at')] = $group->created_at->format(config('setting.format.date'));

            $writer->addRow($row);
        }

        $fileName = 'groups-' . now()->format(config('setting.format.date')) . '.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getMainQueryProperty()
    {
        $query = Group::query();

        if (auth()->user()->isManager()) {
            $query->where('groups.organization_id', auth()->user()->organization_id);
        }

        if ($this->search) {
            $query->search($this->search);
        }

        $colFilters = collect($this->modelFilter);
        $normalFilters = $colFilters->where('normal', true)->all();
        $extraFilters = $colFilters->where('normal', false)->all();

        if (count($normalFilters) && auth()->user()->isAdminOrManager()) {
            $this->filterScope($query, $normalFilters);
        }

        $query->withCount([
            'users as users_count' => function ($query) {
                $query->where('users.role', 'User');
            },
            'users as principals_count' => function ($query) {
                $query->where('users.role', 'Principal');
            },
        ]);

        foreach ($extraFilters as $col) {
            $query->having($col['col'], $col['operator'], $col['query']);
        }

        if ($this->sortBy) {
            $query->sort($this->sortBy, $this->sortOrder);
        }

        return $query;
    }

    public function deleteGroup($groupUuid)
    {
        $group = Group::findByUUIDOrFail($groupUuid);

        $this->authorize('delete', $group);

        $group->users()->sync([]);

        $conversationIds = Conversation::query()
            ->where('organization_id', $group->organization_id)
            ->where(function ($query) use ($group) {
                $query->where('group_id', $group->id)
                    ->orWhere(function ($query) use ($group) {
                        $query->whereNull('group_id')
                            ->where('chat_type', 'single-group-user')
                            ->where('participation_id', $group->id);
                    });
            })->pluck('conversations.id')->toArray();

        ConversationNotification::query()
            ->whereIn('conversation_id', $conversationIds)
            ->delete();

        Message::query()
            ->whereIn('conversation_id', $conversationIds)
            ->delete();

        Conversation::query()
            ->whereIn('id', $conversationIds)
            ->delete();

        $group->delete();

        $this->emitMessage('success', trans('groups.delete_success_msg'));
    }

    public function render()
    {
        $groups = $this->mainQuery->paginate(config('setting.perPage'));

        if (auth()->user()->isAdmin()) {
            $groups->load('organization');
        }

        return view('livewire.groups.index', compact('groups'));
    }
}
