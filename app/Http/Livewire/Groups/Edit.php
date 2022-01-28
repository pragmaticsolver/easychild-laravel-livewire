<?php

namespace App\Http\Livewire\Groups;

use App\Http\Livewire\Component;
use App\Models\Group;

class Edit extends Component
{
    public $uuid = '';
    public $name = '';
    public $organization_id = null;
    public $orgName = '';

    protected $listeners = ['updateValueByKey'];

    public function mount($group)
    {
        $this->authorize('update', $group);

        $this->name = $group->name;
        $this->organization_id = $group->organization_id;
        $this->uuid = $group->uuid;

        $user = auth()->user();
        if ($user->isManager() && $user->organization_id) {
            $this->organization_id = $user->organization_id;
            $this->orgName = $user->organization->name;
        }
    }

    public function updateGroup()
    {
        $this->validate([
            'name' => ['required'],
            'organization_id' => [
                'required',
                'principal_with_org' => function ($attribute, $value, $fail) {
                    $authUser = auth()->user();
                    if ($authUser->isManager() && $authUser->organization_id != $value) {
                        $fail(trans('groups.principal_org_validation'));
                    }
                },
            ],
        ]);

        $data = [
            'name' => $this->name,
            'organization_id' => $this->organization_id,
        ];

        $group = Group::findByUUIDOrFail($this->uuid);
        $this->authorize('update', $group);

        $group->update($data);

        $this->emitMessage('success', trans('groups.update_success'));
    }

    protected function onOrganizationIdUpdated($value)
    {
        $this->organization_id = $value;
    }

    public function render()
    {
        return view('livewire.groups.edit');
    }
}
