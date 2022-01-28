<?php

namespace App\Http\Livewire\Users;

use App\Actions\User\AssignParentToChildAction;
use App\Actions\User\NewPrincipalCreatedAction;
use App\Http\Livewire\Component;
use App\Models\Contract;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\NewUserCreated;
use App\Rules\OrganizationContractRule;
use App\Rules\OrganizationGroupsRule;
use App\Rules\UserCreateWithParentEmailRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Create extends Component
{
    public $given_names = '';
    public $last_name = '';
    public $email = '';
    public $contract_id;
    public $role = 'User';
    public $organization_id = null;
    public $group_id = null;
    public $orgName = '';
    public $dob;
    public $customer_no;
    public $client_no;
    public $parent_email;
    public $groups_id = [];
    public $childrens_id = [];
    public $vendor_view;
    public $eatsOnsite = [];
    public $availability;
    public $allergies;
    public $photo_permission;
    protected $listeners = ['updateValueByKey'];

    public function mount()
    {
        $this->authorize('create', User::class);

        $user = auth()->user();
        if ($user->isManager() && $user->organization_id) {
            $this->organization_id = $user->organization_id;
            $this->orgName = $user->organization->name;
        }

        $this->groups_id = [];

        $this->group_id = null;

        $this->eatsOnsite = $this->assignEatsOnsite();

        $this->availability = null;
        $this->allergies = '';
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $user = auth()->user();
        $org = $user->organization;

        if ($user->isAdmin()) {
            $org = Organization::find($this->organization_id);
        }

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
    }

    public function addUser()
    {
        $this->authorize('create', User::class);

        $availabilityOptions = collect(config('setting.userAvailabilityOptions'))->pluck('value')->all();
        $options = implode(',', $availabilityOptions);

        $this->validate([
            'given_names' => [
                Rule::requiredIf(function () {
                    return $this->role != 'Vendor';
                }),
            ],
            'last_name' => ['required'],
            'email' => [
                Rule::requiredIf(function () {
                    return ! in_array($this->role, ['User', 'Principal']);
                }),
                'email',
                Rule::unique(User::class),
            ],
            'parent_email' => [
                'nullable',
                // Rule::requiredIf(function () {
                //     return $this->role == 'User';
                // }),
                new UserCreateWithParentEmailRule($this->role),
                'email',
            ],
            'dob' => ['nullable'],
            'customer_no' => ['nullable'],
            'client_no' => ['nullable'],
            'organization_id' => [
                'sometimes',
                Rule::requiredIf(function () {
                    return $this->role != 'Admin';
                }),
                'principal_with_org' => function ($attribute, $value, $fail) {
                    $authUser = auth()->user();

                    if ($authUser->isManager() && $authUser->organization_id != $value) {
                        $fail(trans('users.principal_create_validation'));
                    }
                },
            ],
            'group_id' => [
                'sometimes',
                Rule::requiredIf(function () {
                    return $this->role == 'User';
                }),
                new OrganizationGroupsRule($this->organization_id),
            ],
            'contract_id' => [
                'sometimes',
                Rule::requiredIf(function () {
                    return $this->role == 'User';
                }),
                new OrganizationContractRule($this->organization_id),
            ],
            'role' => [
                'required',
                'in:Admin,Manager,Principal,Vendor,User',
                'admin_by_principal' => function ($attribute, $value, $fail) {
                    if ($value == 'Admin' && ! auth()->user()->isAdmin()) {
                        $fail(trans('users.create_admin_user'));
                    }
                },
                'manager_by_principal' => function ($attribute, $value, $fail) {
                    if ($value == 'Manager' && ! auth()->user()->isAdminOrManager()) {
                        $fail(trans('users.create_manager_user'));
                    }
                },
            ],
            'groups_id' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->role === 'Principal';
                }),
                new OrganizationGroupsRule($this->organization_id),
            ],
            // 'childrens_id' => [
            //     'sometimes',
            //     'array',
            //     Rule::requiredIf(function () {
            //         return $this->role === 'Parent';
            //     }),
            // ],
            'availability' => [
                'nullable',
                // Rule::requiredIf(function () {
                //     return $this->role == 'User';
                // }),
                'in:'.$options,
            ],
            'eatsOnsite.*' => [
                'nullable',
                'boolean',
            ],
            'allergies' => [
                'sometimes',
                'max:1000',
            ],
            'vendor_view' => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->role === 'Vendor';
                }),
                'in:all,summary',
            ],
        ]);

        $settings = [];
        if ($this->role === 'User') {
            $settings['eats_onsite'] = $this->eatsOnsite;
            $settings['allergies'] = $this->allergies;

            if ($this->availability) {
                $settings['availability'] = $this->availability;
            }

            $settings['attendance_token'] = Str::random(32);

            $settings['client_no'] = $this->client_no;
        }

        if ($this->role === 'Vendor') {
            $settings['vendor_view'] = $this->vendor_view;
        }

        $data = [
            'given_names' => $this->given_names,
            'last_name' => $this->last_name,
            'username' => $this->role == 'Principal' ? generateUserName($this->given_names, $this->last_name) : null,
            'email' => in_array($this->role, ['Principal', 'User']) ? null : $this->email,
            'organization_id' => in_array($this->role, ['Admin', 'Parent']) ? null : $this->organization_id,
            'group_id' => $this->group_id,
            'role' => $this->role,
            'groups_id' => $this->groups_id,
            // 'password' => Str::random(config('setting.auth.passwordLength')),
            // 'token' => Str::random(config('setting.auth.tokenLength')),
            'settings' => $settings,
            'dob' => $this->dob,
            'customer_no' => $this->customer_no,
            'contract_id' => $this->role == 'User' ? $this->contract_id : null,
            'photo_permission' => $this->photo_permission ? true : false,
        ];

        $user = User::create(
            collect($data)->except(['group_id', 'groups_id', 'parent_email'])->toArray()
        );

        $possibleGroups = [];
        if ($this->role === 'Principal') {
            $possibleGroups = Group::where('organization_id', $this->organization_id)
                ->whereIn('id', $this->groups_id)
                ->pluck('id')
                ->toArray();
        }

        // if ($this->role === 'Parent') {
        //     $childrenItems = User::query()
        //         ->where('organization_id', $this->organization_id)
        //         ->where('role', 'User')
        //         ->whereIn('id', $this->childrens_id)
        //         ->pluck('id')
        //         ->toArray();

        //     $user->childrens()->sync($childrenItems);
        // }

        if ($this->role === 'User') {
            if (Arr::has($data, 'group_id')) {
                $possibleGroups = Group::where('organization_id', $this->organization_id)
                    ->where('id', $this->group_id)
                    ->pluck('id')
                    ->toArray();
            }
        }

        if (in_array($this->role, ['User', 'Principal'])) {
            $user->groups()->sync($possibleGroups);
        }

        session()->flash('success', trans('users.create_success', ['name' => $user->full_name]));

        if ($user->role == 'Principal') {
            auth()->user()->jobs()->updateOrCreate([
                'related_type' => User::class,
                'related_id' => $user->id,
                'action' => NewPrincipalCreatedAction::class,
            ], [
                'user_ids' => [],
                'due_at' => now()->addMinutes(2),
                'data' => [],
            ]);
        } elseif ($user->role == 'User') {
            if ($this->parent_email) {
                if (! $parent = User::where('email', $this->parent_email)->first()) {
                    $parent = User::make([
                        'email' => $this->parent_email,
                        'role' => 'Parent',
                    ]);
                }

                AssignParentToChildAction::run($user, $parent);
            }
        } else {
            auth()->user()->jobs()->updateOrCreate([
                'related_type' => User::class,
                'related_id' => $user->id,
                'action' => NewUserCreated::class,
            ], [
                'user_ids' => [$user->id],
                'due_at' => now()->addMinutes(2),
                'data' => [],
            ]);
        }

        return redirect(route('users.edit', $user->uuid));
    }

    private function assignEatsOnsite($eatsOnsite = null)
    {
        if (! $eatsOnsite) {
            $eatsOnsite = $this->eatsOnsiteOrgDefaults;
        }

        $orgEatsOnsiteSetting = $this->eatsOnsiteOrgDefaults;

        $meals = ['breakfast', 'lunch', 'dinner'];

        foreach ($meals as $meal) {
            if (! $orgEatsOnsiteSetting[$meal]) {
                $eatsOnsite[$meal] = null;
            }

            if ($eatsOnsite[$meal]) {
                $eatsOnsite[$meal] = null;
            }
        }

        return $eatsOnsite;
    }

    public function updateValueByKey($data)
    {
        parent::updateValueByKey($data);
    }

    public function updatedRole($value)
    {
        if ($value === 'Principal') {
            $this->updateGroups($this->organization_id);
        }

        // if ($value === 'Parent') {
        //     $this->updateOrgUsers($this->organization_id);
        // }
    }

    public function updatedOrganizationId($value)
    {
        $this->onOrganizationIdUpdated($value);

        $this->eatsOnsite = $this->assignEatsOnsite();
    }

    protected function onOrganizationIdUpdated($value)
    {
        $this->emit('users.create.organization_id.updated', $value);

        if ($this->role === 'Principal') {
            $this->updateGroups($value);
        }

        // if ($this->role === 'Parent') {
        //     $this->updateOrgUsers($value);
        // }

        if ($this->role === 'User') {
            $this->group_id = null;
        }
    }

    private function updateGroups($organization_id)
    {
        $this->groups_id = [];
        $this->group_id = null;

        $this->emit('users.create.selected-groups.updated', [
            'key' => 'selected',
            'value' => $this->groups_id,
        ]);

        $this->emit('users.create.groups.extra-limitor.updated', [
            'key' => 'extraLimitor',
            'value' => [
                'organization_id' => $this->organization_id,
            ],
        ]);
    }

    private function updateOrgUsers($organization_id)
    {
        $this->childrens_id = [];

        $this->emit('users.create.selected-childrens.updated', [
            'key' => 'selected',
            'value' => $this->childrens_id,
        ]);

        $this->emit('users.create.childrens.extra-limitor.updated', [
            'key' => 'extraLimitor',
            'value' => [
                'role' => 'User',
                'organization_id' => $this->organization_id,
            ],
        ]);
    }

    public function render()
    {
        $availabilityOptions = config('setting.userAvailabilityOptions');

        $contracts = Contract::query()
            ->where('organization_id', $this->organization_id)
            ->get();

        return view('livewire.users.create', compact('availabilityOptions', 'contracts'));
    }
}
