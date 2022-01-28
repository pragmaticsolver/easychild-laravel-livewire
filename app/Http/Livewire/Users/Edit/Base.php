<?php

namespace App\Http\Livewire\Users\Edit;

use App\Actions\User\NewPrincipalCreatedAction;
use App\Http\Livewire\Component;
use App\Models\Contract;
use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\NewUserCreated;
use App\Rules\OrganizationContractRule;
use App\Rules\OrganizationGroupsRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class Base extends Component
{
    public $given_names = '';
    public $last_name = '';
    public $email = '';
    public $role = 'User';
    public $contract_id;
    public $dob;
    public $customer_no;
    public $client_no;
    public $organization_id = null;
    public $group_id = null;
    public $orgName = '';
    public $uuid;

    public $vendor_view;
    public $attendance_token;

    public $groups_id = [];
    // public $childrens_id = [];

    public $eatsOnsite;
    public $availability;
    public $allergies;

    public User $user;

    protected $listeners = [
        'updateValueByKey',
    ];

    public function mount($user)
    {
        $this->authorize('update', $user);

        $this->user = $user;

        $this->given_names = $user->given_names;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->organization_id = $user->organization_id;
        $this->uuid = $user->uuid;
        $this->contract_id = $user->contract_id;
        $this->dob = $user->dob;
        $this->customer_no = $user->customer_no;

        // $organizationSettings = optional($user->organization)->settings;
        $userSettings = $user->settings;

        if (! $userSettings) {
            $userSettings = [];
        }

        $this->client_no = $this->getValueByKey($userSettings, 'client_no', '');

        $this->eatsOnsite = $this->getValueByKey($userSettings, 'eats_onsite', $this->eatsOnsiteOrgDefaults);

        $this->availability = $this->getValueByKey($userSettings, 'availability', null);
        $this->allergies = $this->getValueByKey($userSettings, 'allergies', '');

        $this->vendor_view = $this->getValueByKey($userSettings, 'vendor_view', null);
        $this->attendance_token = $this->getValueByKey($userSettings, 'attendance_token', Str::random(32));

        $this->groups_id = $user->groups->pluck('id')->toArray();

        // if ($user->isParent()) {
        //     $this->childrens_id = $user->childrens->pluck('id')->toArray();
        // }

        if ($user->isUser() && $user->userGroup()) {
            $this->group_id = $user->userGroup()->id;
        }

        if ($user->organization_id) {
            $this->orgName = $user->organization->name;
        }
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $org = Organization::find($this->organization_id);
        } else {
            $org = $user->organization;
        }

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
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
    }

    protected function onOrganizationIdUpdated($value)
    {
        $this->emit('users.edit.organization_id.updated', $value);

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

        $this->emit('users.edit.selected-groups.updated', [
            'key' => 'selected',
            'value' => $this->groups_id,
        ]);

        $this->emit('users.edit.groups.extra-limitor.updated', [
            'key' => 'extraLimitor',
            'value' => [
                'organization_id' => $this->organization_id,
            ],
        ]);
    }

    private function updateOrgUsers($organization_id)
    {
        $this->childrens_id = [];

        $this->emit('users.edit.selected-childrens.updated', [
            'key' => 'selected',
            'value' => $this->childrens_id,
        ]);

        $this->emit('users.edit.childrens.extra-limitor.updated', [
            'key' => 'extraLimitor',
            'value' => [
                'role' => 'User',
                'organization_id' => $this->organization_id,
            ],
        ]);
    }

    public function resetUserPassword()
    {
        $user = User::findByUUIDOrFail($this->uuid);
        $this->authorize('update', $user);

        if ($user->role == 'Principal' && ! $user->username) {
            $user->update([
                'username' => generateUserName($this->given_names, $this->last_name),
            ]);
        }

        // $user->notify(new NewUserCreated($user));

        auth()->user()->jobs()->updateOrCreate([
            'related_type' => User::class,
            'related_id' => $user->id,
            'action' => $user->isPrincipal() ? NewPrincipalCreatedAction::class : NewUserCreated::class,
        ], [
            'user_ids' => [$user->id],
            'due_at' => now()->addMinutes(5),
            'data' => [
                'password-reset' => true,
            ],
        ]);

        $this->emitMessage('success', trans('users.password_change_success'));
    }

    public function updateUser()
    {
        if (auth()->user()->isPrincipal()) {
            return $this->emitMessage('error', trans('extras.admin_and_manager_only'));
        }

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
                'nullable',
                'email',
                Rule::unique(User::class)->ignore($this->uuid, 'uuid'),
                Rule::requiredIf(function () {
                    return ! in_array($this->role, ['User', 'Principal']);
                }),
            ],
            'organization_id' => [
                'sometimes',
                Rule::requiredIf(function () {
                    return $this->role != 'Admin';
                }),
                'principal_with_org' => function ($attribute, $value, $fail) {
                    $authUser = auth()->user();
                    if (($authUser->isManager() || $authUser->isPrincipal()) && $authUser->organization_id != $value) {
                        $fail(trans('users.principal_update_validation'));
                    }
                },
            ],
            'group_id' => [
                'sometimes',
                Rule::requiredIf(function () {
                    return $this->role === 'User';
                }),
                new OrganizationGroupsRule($this->organization_id),
            ],
            'groups_id' => [
                'sometimes',
                'array',
                Rule::requiredIf(function () {
                    return $this->role === 'Principal';
                }),
                new OrganizationGroupsRule($this->organization_id),
            ],
            'dob' => ['nullable'],
            'client_no' => ['nullable'],
            'customer_no' => ['nullable'],
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
                'only_user_by_principal' => function ($attribute, $value, $fail) {
                    if ($value != 'User' && auth()->user()->isPrincipal()) {
                        $fail(trans('users.create_manager_user'));
                    }
                },
            ],
            'availability' => [
                'nullable',
                // Rule::requiredIf(function () {
                //     return $this->role == 'User';
                // }),
                'in:'.$options,
            ],
            // 'childrens_id' => [
            //     'sometimes',
            //     'array',
            //     Rule::requiredIf(function () {
            //         return $this->role === 'Parent';
            //     }),
            // ],
            'allergies' => [
                'sometimes',
                'max:1000',
            ],
            'eatsOnsite.*' => [
                'sometimes',
                'nullable',
                // Rule::requiredIf(function () {
                //     return $this->role === 'User';
                // }),
                'boolean',
            ],
            'vendor_view' => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->role === 'Vendor';
                }),
                'in:all,summary',
            ],
            'attendance_token' => [
                'nullable',
                Rule::requiredIf(function () {
                    return $this->role === 'User';
                }),
            ],
        ]);

        $user = User::findByUUIDOrFail($this->uuid);
        $this->authorize('update', $user);

        $settings = $user->settings;
        if (! $settings) {
            $settings = [];
        }

        if ($this->role === 'User') {
            $settings['eats_onsite'] = $this->eatsOnsite;
            $settings['availability'] = $this->availability;

            if (! $this->availability) {
                unset($settings['availability']);
            }

            $settings['client_no'] = $this->client_no;
            $settings['allergies'] = $this->allergies;
            $settings['attendance_token'] = $this->attendance_token;
        }

        if ($this->role === 'Vendor') {
            $settings['vendor_view'] = $this->vendor_view;
        }

        $data = [
            'given_names' => $this->given_names,
            'last_name' => $this->last_name,
            'email' => in_array($this->role, ['Principal', 'User']) ? null : $this->email,
            'organization_id' => in_array($this->role, ['Admin', 'Parent']) ? null : $this->organization_id,
            'role' => $this->role,
            'settings' => $settings,
            'dob' => $this->dob,
            'customer_no' => $this->customer_no,
            'contract_id' => $this->role == 'User' ? $this->contract_id : null,
        ];

        if ($this->role != 'Principal') {
            $data['username'] = null;
        }

        if ($this->user->role != 'Principal' && $this->role == 'Principal') {
            $data['username'] = generateUserName($this->given_names, $this->last_name);
        }

        if ($this->role == 'Principal' && ! $user->username) {
            $data['username'] = generateUserName($this->given_names, $this->last_name);
        }

        $user->update($data);

        // update all allergy data for schedules later after today
        $scheduleFindDate = now()->format('Y-m-d');

        $orgSettings = optional($user->organization)->settings;
        if (! $orgSettings) {
            $orgSettings = [];
        }

        if (Arr::has($orgSettings, 'food_lock_time')) {
            // $foodLockTime = explode(':', $orgSettings['food_lock_time']);
            // $lockTimeEnd = now()->setHour($foodLockTime[0])->setMinute($foodLockTime[1]);
            $lockTimeEnd = now()->setTimeFromTimeString($orgSettings['food_lock_time']);

            if (now()->isAfter($lockTimeEnd)) {
                $scheduleFindDate = now()->addDay()->format('Y-m-d');
            }
        }

        if ($this->role === 'User') {
            $user->schedules()
                ->where('date', '>=', $scheduleFindDate)
                ->update(['allergy' => $this->allergies]);
        }

        $possibleGroups = [];
        if ($this->role === 'Principal') {
            $possibleGroups = Group::where('organization_id', $this->organization_id)
                ->whereIn('id', $this->groups_id)
                ->pluck('id')
                ->toArray();
        }

        // $this->setChildrensToParent($user);

        if ($this->role === 'User') {
            if ($this->role === 'User' && $this->group_id) {
                $possibleGroups = Group::where('organization_id', $this->organization_id)
                    ->where('id', $this->group_id)
                    ->pluck('id')
                    ->toArray();
            }
        }

        $user->groups()->sync($possibleGroups);

        $this->emitMessage('success', trans('users.update_success'));
    }

    private function setChildrensToParent($user)
    {
        $childrenItems = [];

        if ($this->role === 'Parent') {
            $childrenItems = User::query()
                ->where('organization_id', $this->organization_id)
                ->where('role', 'User')
                ->whereIn('id', $this->childrens_id)
                ->pluck('id')
                ->toArray();
        }

        $user->childrens()->sync($childrenItems);
    }

    private function assignEatsOnsite()
    {
        $eatsOnsite = $this->eatsOnsite;
        $userEatsOnsite = [];

        $orgEatsOnsiteSetting = $this->eatsOnsiteOrgDefaults;

        $meals = ['breakfast', 'lunch', 'dinner'];

        foreach ($meals as $meal) {
            $userEatsOnsite[$meal] = null;

            if ($orgEatsOnsiteSetting[$meal]) {
                if (Arr::has($eatsOnsite, $meal)) {
                    $userEatsOnsite[$meal] = $eatsOnsite[$meal];
                }
            }
        }

        return $userEatsOnsite;
    }

    public function render()
    {
        $availabilityOptions = config('setting.userAvailabilityOptions');

        $contracts = collect();

        if ($this->role == 'User') {
            $contracts = Contract::query()
                ->where('organization_id', $this->organization_id)
                ->get();
        }

        return view('livewire.users.edit.base', compact('availabilityOptions', 'contracts'));
    }
}
