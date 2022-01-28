<?php

namespace App\Http\Livewire\Groups;

use App\Http\Livewire\Component;
use App\Models\Group;

class Create extends Component
{
    public $name = '';
    public $organization_id = null;
    public $orgName = '';
    public $data = [];

    protected $listeners = ['updateValueByKey'];

    public function mount($mainTitle = null)
    {
        $this->authorize('create', Group::class);

        $this->data['mainTitle'] = $mainTitle;

        $user = auth()->user();
        if ($user->isManager() && $user->organization_id) {
            $this->organization_id = $user->organization_id;
            $this->orgName = $user->organization->name;
        }
    }

    public function addGroup()
    {
        $this->authorize('create', Group::class);

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

        $group = Group::create($data);

        session()->flash('success', trans('groups.create_success', ['name' => $group->name]));

        redirect(route('groups.edit', $group->uuid));
    }

    protected function onOrganizationIdUpdated($value)
    {
        $this->organization_id = $value;
    }

    public function render()
    {
        return view('livewire.groups.create');
    }
}
