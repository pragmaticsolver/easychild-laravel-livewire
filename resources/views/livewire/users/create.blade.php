<div class="sm:mx-auto sm:w-full sm:max-w-4xl">
    <form wire:submit.prevent="addUser">
        <div class="flex flex-wrap -mx-4 lg:-mx-8">
            <div class="w-full md:w-1/2 px-4 lg:px-8 mb-6">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('users.role') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <select wire:model="role" id="role" name="role" required class="form-select select-field @error('role') error @enderror">
                            @if (auth()->user()->isAdmin())
                                <option value="Admin">{{ trans('extras.role_admin') }}</option>
                            @endif
                            @if (auth()->user()->isAdminOrManager())
                                <option value="Manager">{{ trans('extras.role_manager') }}</option>
                            @endif
                            {{-- <option value="Parent">{{ trans('extras.role_parent') }}</option> --}}
                            @if (auth()->user()->isAdminOrManager())
                                <option value="Principal">{{ trans('extras.role_principal') }}</option>
                                <option value="Vendor">{{ trans('extras.role_vendor') }}</option>
                            @endif
                            <option value="User">{{ trans('extras.role_user') }}</option>
                        </select>
                    </div>

                    @error('role')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if ($role != 'Vendor')
                    <div class="mt-6">
                        <label for="given_names" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.given_name') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="given_names" id="given_names" name="given_names" type="text" required autocomplete="off" class="text-field @error('given_names') error @enderror" />
                        </div>

                        @error('given_names')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="mt-6">
                    <label for="last_name" class="block text-sm font-medium text-gray-700 leading-5">
                        @if ($role == 'Vendor')
                            <span>{{ trans('users.company_name') }}</span>
                        @else
                            <span>{{ trans('users.last_name') }}</span>
                        @endif
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="last_name" id="last_name" name="last_name" type="text" required autocomplete="off" class="text-field @error('last_name') error @enderror" />
                    </div>

                    @error('last_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($role == 'User')
                    <div class="flex flex-wrap -mx-2">
                        <div class="w-full px-2 sm:w-1/2 mt-6">
                            <label for="user_dob" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.dob') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <x-date-picker
                                    id="user_dob"
                                    name="dob"
                                    wire:model.defer="dob"
                                    class="@error('given_names') error @enderror"
                                    :current-value="$dob"
                                ></x-date-picker>
                            </div>

                            @error('dob')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="w-full px-2 sm:w-1/2 mt-6">
                            <label for="contract" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.contract_label') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <select wire:model.defer="contract_id" id="contract_id" name="contract_id" required class="form-select select-field @error('contract_id') error @enderror">
                                    <option value="">{{ trans('users.select_contract') }}</option>
                                    @foreach($contracts as $contractItem)
                                        <option value="{{ $contractItem->id }}">{{ $contractItem->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @error('contract_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex flex-wrap -mx-2">
                        <div class="w-full px-2 sm:w-1/2 mt-6">
                            <label for="customer_no" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.customer_no') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <input wire:model.defer="customer_no" id="customer_no" name="customer_no" type="text" autocomplete="off" class="text-field @error('customer_no') error @enderror" />
                            </div>

                            @error('customer_no')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="w-full px-2 sm:w-1/2 mt-6">
                            <label for="client_no" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.client_no') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <input wire:model.defer="client_no" id="client_no" name="client_no" type="text" autocomplete="off" class="text-field @error('client_no') error @enderror" />
                            </div>

                            @error('client_no')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label for="parent_email" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.parent_email') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="parent_email" id="parent_email" name="parent_email" type="email" autocomplete="off" class="text-field @error('parent_email') error @enderror" />
                        </div>

                        @error('parent_email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>

            <div class="w-full md:w-1/2 px-4 lg:px-8 mb-6">
                @if(! in_array($role, ['User', 'Principal']))
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.email') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            <input wire:model.defer="email" id="email" name="email" type="email" required autocomplete="off" class="text-field @error('email') error @enderror" />
                        </div>

                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($role != 'Admin')
                    <div class="mt-6">
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.organization') }}
                        </label>

                        @if(auth()->user()->isAdmin())
                            <div class="mt-1 rounded-md shadow-sm">
                                @livewire('components.search-select', [
                                    'selected' => $organization_id,
                                    'emitUpWhenUpdated' => 'organization_id',
                                    'provider' => [
                                        'model' => 'organization',
                                        'key' => 'id',
                                        'text' => 'name',
                                    ],
                                ])
                            </div>
                        @else
                            <div class="mt-1 rounded-md shadow-sm bg-white">
                                <span class="text-field">{{ $orgName }}</span>
                            </div>
                        @endif

                        @error('organization_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($role == 'User')
                    <div class="mt-6">
                        <label for="group_id" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.group') }}
                        </label>

                        <div class="mt-1 rounded-md shadow-sm">
                            @livewire('components.search-select', [
                                'emitUpWhenUpdated' => 'group_id',
                                'provider' => [
                                    'model' => 'group',
                                    'key' => 'id',
                                    'text' => 'name',
                                    'limitKey' => 'organization_id',
                                    'limitValue' => $organization_id,
                                    'updateOn' => 'users.create.organization_id.updated'
                                ],
                                'selected' => $group_id,
                            ])
                        </div>

                        @error('group_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                @if ($role == 'Principal')
                    <div class="mt-6">
                        <label for="groups_id" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('calendar-events.groups_label') }}
                        </label>

                        @livewire('components.search-multi-select', [
                            'targetModel' => 'group',
                            'selected' => $groups_id,
                            'displayKey' => 'name',
                            'orderBy' => 'name',
                            'enableSearch' => false,
                            'wireKey' => 'users-create-groups',
                            'emitUpWhenUpdated' => [
                                'groups_id' => 'selected',
                            ],
                            'listenToEmit' => [
                                'users.create.selected-groups.updated',
                                'users.create.groups.extra-limitor.updated',
                            ],
                            'extraLimitor' => [
                                'organization_id' => $this->organization_id,
                            ],
                        ])

                        @error('groups_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif


                {{-- @if (false && $role == 'Parent')
                    <div class="mt-6">
                        <label for="childrens_id" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.childrens') }}
                        </label>

                        @livewire('components.search-multi-select', [
                            'targetModel' => 'user',
                            'selected' => $childrens_id,
                            'displayKey' => 'full_name',
                            'orderBy' => 'given_names',
                            'enableSearch' => true,
                            'wireKey' => 'users-create-users',
                            'emitUpWhenUpdated' => [
                                'childrens_id' => 'selected',
                            ],
                            'listenToEmit' => [
                                'users.create.selected-childrens.updated',
                                'users.create.childrens.extra-limitor.updated',
                            ],
                            'extraLimitor' => [
                                'role' => 'User',
                                'organization_id' => $this->organization_id,
                            ],
                        ])

                        @error('childrens_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif --}}

                <div>
                    @if ($role == 'User')
                        <hr class="mt-6 border-t">

                        @include('components.schedule-settings', [
                            'eatsOnsiteType' => 'user',
                            'eatsOnsiteOrgDefaults' => $this->eatsOnsiteOrgDefaults,
                        ])

                        <div class="mt-6">
                            <label for="photo_permission" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.photo_permission') }}
                            </label>
                            <x-switch :disabled="false" wire:model="photo_permission"></x-switch>
                        </div>
                        
                        <div class="mt-6">
                            <label for="allergies" class="block text-sm font-medium text-gray-700 leading-5">
                            {{ trans('users.allergies') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <textarea wire:model.defer="allergies" id="allergies" name="allergies" row="7" class="text-field resize-none h-16 @error('allergies') error @enderror"></textarea>
                            </div>

                            @error('allergies')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>

                <div>
                    @if ($role == 'Vendor')
                        <div class="mt-6">
                            <label for="vendor_view" class="block text-sm font-medium text-gray-700 leading-5">
                                {{ trans('users.vendor_view_type') }}
                            </label>

                            <div class="mt-1 rounded-md shadow-sm">
                                <select wire:model="vendor_view" id="vendor_view" name="vendor_view" required class="form-select select-field @error('vendor_view') error @enderror">
                                    <option value="">{{ trans('users.select_view_type') }}</option>
                                    <option value="all">{{ trans('users.vendor_view_all') }}</option>
                                    <option value="summary">{{ trans('users.vendor_view_summary') }}</option>
                                </select>
                            </div>

                            @error('vendor_view')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="addUser"
            >
                {{ trans('users.add') }}
            </button>
        </div>
    </form>
</div>
