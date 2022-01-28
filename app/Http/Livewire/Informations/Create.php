<?php

namespace App\Http\Livewire\Informations;

use App\Http\Livewire\Component;
use App\Models\Information;
use App\Models\User;
use App\Notifications\InformationAddedNotification;
use App\Rules\OrganizationGroupsRule;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $title;
    public $file;
    public $roles = [
        'User' => true,
        'Manager' => false,
        'Principal' => false,
        'Vendor' => false,
    ];
    public $groups_id = [];

    protected $listeners = ['updateValueByKey'];

    public function addInformation()
    {
        $mimes = implode(',', [
            'pdf',
            'odt', 'doc', 'docx',
            'xps',
            'txt', 'rtx',
            'ods', 'xls', 'xlsx',
            'odp', 'ppt', 'pptx',
        ]);

        $this->validate([
            'title' => 'required',
            'file' => 'required|file|max:10240|mimes:'.$mimes, // 10 MB size max
            'roles.*' => 'required|boolean',
            'groups_id' => ['array', new OrganizationGroupsRule()],
        ]);

        $user = auth()->user();

        if (! $user->isManager()) {
            return $this->emitMessage('error', trans('informations.add_error'));
        }

        $roles = [];
        $availableRoles = ['User', 'Manager', 'Principal', 'Vendor'];
        foreach ($availableRoles as $role) {
            if ($this->roles[$role]) {
                $roles[] = $role;
            }
        }

        $groupList = [];
        if (count($this->groups_id)) {
            foreach ($this->groups_id as $g) {
                $groupList[] = (int) $g;
            }
        }

        $fileName = $this->file->getClientOriginalName();
        $information = Information::create([
            'title' => $this->title,
            'roles' => $roles,
            'groups' => $groupList,
            'organization_id' => $user->organization_id,
            'creator_id' => $user->id,
            'file' => $fileName,
        ]);

        $dirName = "files/".$information->uuid;
        $this->file->storeAs($dirName, $fileName);

        $this->sendNotification($information);

        session()->flash('success', trans('informations.create_success'));

        return redirect()->route('informations.index');
    }

    private function sendGroupsResetEvent()
    {
        $this->emit('calendar-events.create.selected-groups.updated', [
            'key' => 'selected',
            'value' => $this->groups_id,
        ]);
    }

    private function sendNotification($information)
    {
        $users = User::query()
            ->where('users.id', '!=', auth()->id())
            ->where(function ($query) use ($information) {
                $query->when(in_array('Manager', $information->roles), function ($query) use ($information) {
                    $query->where(function ($query) use ($information) {
                        $query->where('users.role', 'Manager')
                            ->where('users.organization_id', $information->organization_id);
                    });
                });

                $query->when(in_array('Vendor', $information->roles), function ($query) use ($information) {
                    $query->orWhere(function ($query) use ($information) {
                        $query->where('users.role', 'Vendor')
                            ->where('users.organization_id', $information->organization_id);
                    });
                });

                $query->when(in_array('Principal', $information->roles), function ($query) use ($information) {
                    $query->orWhere(function ($query) use ($information) {
                        $query->where('users.role', 'Principal')
                            ->where('users.organization_id', $information->organization_id);

                        $query->when($information->groups && count($information->groups), function ($query) use ($information) {
                            $query->whereIn('users.id', function ($query) use ($information) {
                                $query->select('group_user.user_id')
                                    ->from('group_user')
                                    ->whereIn('group_user.group_id', $information->groups);
                            });
                        });
                    });
                });

                $query->when(in_array('User', $information->roles), function ($query) use ($information) {
                    $query->orWhere(function ($query) use ($information) {
                        $query->where('users.role', 'Parent')
                            ->whereIn('users.id', function ($query) use ($information) {
                                $query->select('parent_child.parent_id')
                                    ->from('parent_child')
                                    ->when($information->groups && count($information->groups), function ($query) use ($information) {
                                        $query->whereIn('parent_child.child_id', function ($query) use ($information) {
                                            $query->select('group_user.user_id')
                                                ->from('group_user')
                                                ->whereIn('group_user.group_id', $information->groups);
                                        });
                                    })
                                    ->whereIn('parent_child.child_id', function ($query) use ($information) {
                                        $query->select('u1.id')
                                            ->from('users as u1')
                                            ->where('u1.role', 'User')
                                            ->where('u1.organization_id', $information->organization_id);
                                    });
                            });
                    });
                });
            })
            ->pluck('users.id')
            ->all();

        if ($users && count($users)) {
            auth()->user()->jobs()->create([
                'related_type' => Information::class,
                'related_id' => $information->id,
                'action' => InformationAddedNotification::class,
                'user_ids' => $users,
                'due_at' => now()->addMinutes(1),
                'data' => [
                    'type' => 'create',
                ],
            ]);
        }
    }

    public function render()
    {
        return view('livewire.informations.create');
    }
}
