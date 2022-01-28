<?php

namespace App\Http\Livewire\Users;

use App\Http\Livewire\Component;
use App\Models\Group;
use App\Models\User;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Index extends Component
{
    use WithPagination, HasSorting, HasIndexPageFilters;

    public $placeholder;
    public $search = '';
    public $sortBy = '';
    public $sortOrder = 'ASC';
    public $role;
    public $group = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => null],
        'group' => ['except' => ''],
        'sortBy' => ['except' => ''],
        'sortOrder' => ['except' => 'ASC'],
        'page',
    ];

    protected $listeners = [
        'principalGroupUpdated' => '$refresh',
    ];

    public $colFilters = [
        'user_given_names' => [
            'filter' => null,
            'query' => null,
        ],
        'user_role' => [
            'filter' => null,
            'query' => null,
        ],
        'organization_name_add_group' => [
            'filter' => null,
            'query' => null,
        ],
        'photo_permission' => [
            'filter' => null,
            'query' => null,
        ]
    ];

    protected $colFiltersMap = [
        'user_given_names' => [
            'key' => 'users.given_names',
            'normal' => true,
        ],
        'user_role' => [
            'key' => 'users.role',
            'normal' => true,
        ],
        'organization_name_add_group' => [
            'key' => 'name',
            'normal' => false,
        ],
        'photo_permission' => [
            'key' => 'photo_permission',
            'normal' => false,
        ]
    ];

    protected $sortableCols = [
        'given_names',
        'created_at',
        'organization_name',
        'role',
    ];

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function mount()
    {
        $this->authorize('viewAny', User::class);

        if (! $this->sortBy) {
            $this->sortBy = $this->sortableCols[0];
        }

        if (request()->has('group') && request('group')) {
            $this->group = request('group');
        }

        $this->placeholder = trans('users.search');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteUser($uuid)
    {
        $user = User::findByUUIDOrFail($uuid);

        $this->authorize('delete', $user);

        $user->delete();

        $this->emitMessage('success', trans('users.delete_success', [
            'name' => $user->full_name,
        ]));
    }

    public function download()
    {
        $users = $this->mainQuery->get();

        if (! $users->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/".auth()->user()->uuid.'.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid.'.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($users as $user) {
            $row = [
                trans('excel.users.id') => $user->id,
                trans('excel.users.given_names') => $user->given_names,
                trans('excel.users.last_name') => $user->last_name,
                trans('excel.users.role') => trans('extras.role_'.Str::lower($user->role)),
                trans('excel.users.email') => $user->email,
            ];

            if (auth()->user()->isAdmin()) {
                if ($user->organization_id) {
                    $row[trans('excel.users.organization')] = $user->organization_name.' - ('.$user->organization_id.')';
                } else {
                    $row[trans('excel.users.organization')] = '-';
                }
            }

            $row[trans('users.dob')] = null;
            $row[trans('users.customer_no')] = null;
            if ($user->isuser()) {
                if ($user->dob) {
                    $row[trans('users.dob')] = Carbon::parse($user['dob'])->format(config('setting.format.date'));
                }

                if ($user->customer_no) {
                    $row[trans('users.customer_no')] = $user->customer_no;
                }
            }

            $row[trans('excel.users.created_at')] = $user->created_at->format(config('setting.format.date'));

            $writer->addRow($row);
        }

        $fileName = 'organizations-'.now()->format(config('setting.format.date')).'.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    public function getMainQueryProperty()
    {
        $user = auth()->user();
        $userRole = (string) Str::of($user->role)->lower();

        $query = User::query();

        if ($userRole === 'manager') {
            $query->where('users.organization_id', $user->organization_id);
        }

        if ($userRole === 'principal') {
            $principalGroup = $user->principal_current_group;

            $query->where('users.organization_id', $user->organization_id)
                ->whereIn('users.id', function ($query) use ($principalGroup) {
                    $query->select('group_user.user_id')
                        ->from('group_user')
                        ->where('group_user.group_id', $principalGroup->id);
                })
                ->where('users.role', 'User');

            $this->group = '';
        } else {
            if ($this->group) {
                $groupQuery = Group::query()
                    ->whereName($this->group);

                if ($userRole == 'Manager') {
                    $groupQuery->where('organization_id', $user->organization_id);
                }

                $group = $groupQuery->first();

                if ($group) {
                    $query
                        ->where('users.role', 'User')
                        ->whereIn('users.id', function ($query) use ($group) {
                            $query->select('group_user.user_id')
                                ->from('group_user')
                                ->where('group_user.group_id', $group->id);
                        });
                } else {
                    $this->group = '';
                }
            }

            // if ($this->role) {
            //     $query->where('role', $this->role);
            // }
        }

        if ($this->search) {
            $query->search($this->search);
        }

        $colFilters = collect($this->modelFilter);
        $normalFilters = $colFilters->where('normal', true)->all();
        $extraFilters = $colFilters->where('normal', false)->all();

        foreach ($extraFilters as $col) {
            if ($col['key'] == 'organization_name_add_group') {
                $query->where(function ($query) use ($col) {
                    $query->whereIn('users.organization_id', function ($query) use ($col) {
                        $query->select('organizations.id')
                            ->from('organizations')
                            ->whereColumn('organizations.id', 'users.organization_id')
                            ->where(function ($q) use ($col) {
                                $q->where('name', $col['operator'], $col['query'])
                                    ->orWhere('address', $col['operator'], $col['query']);
                            });
                    });

                    $query->orWhereIn('users.id', function ($query) use ($col) {
                        $query->select('group_user.user_id')
                            ->from('group_user')
                            ->whereColumn('group_user.user_id', 'users.id')
                            ->join('groups', 'groups.id', 'group_user.group_id')
                            ->where('name', $col['operator'], $col['query']);
                    });
                });

            } else if($col['key'] == 'photo_permission') {
                $query->where(function($query) use ($col) {
                    $query->where('role', $col['operator'], 'User')
                        ->where('photo_permission', $col['operator'], $col['query']);
                });
            }
        }

        $query->addSelect([
            'organization_name' => DB::table('organizations')
                ->whereColumn('organizations.id', 'users.organization_id')
                ->select('name')
                ->limit(1),
        ]);

        if (count($normalFilters) && $user->isAdminOrManager()) {
            $this->filterScope($query, $normalFilters);
        }

        if ($this->sortBy) {
            $query->sort($this->sortBy, $this->sortOrder);
        }

        return $query;
    }

    public function render()
    {
        $users = $this->mainQuery->paginate(config('setting.perPage'));
        $users->load('organization', 'groups');

        return view('livewire.users.index', compact('users'));
    }
}
