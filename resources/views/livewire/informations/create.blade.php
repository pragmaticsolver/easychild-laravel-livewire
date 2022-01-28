<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <form wire:submit.prevent="addInformation">
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('informations.title_field') }}
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="title" id="title" name="title" type="text" required autocomplete="off" class="text-field @error('title') error @enderror" />
            </div>

            @error('title')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>


        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('informations.role') }}
            </label>

            <div class="mt-4 flex flex-wrap -mx-2">
                @foreach(['Manager', 'Principal', 'Vendor', 'User'] as $role)
                    <div class="p-2 flex items-center sm:w-1/2">
                        <x-switch wire:model.defer="roles.{{ $role }}"></x-switch>

                        <label class="ml-4 text-sm font-medium text-gray-700 leading-5">
                            {{ trans('extras.role_' . Str::lower($role)) }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-5">
            <label for="groups_id" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.group') }}
            </label>
            <p class="text-xs mb-2">{{ trans('calendar-events.groups_selection_label') }}</p>

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
                    'calendar-events.create.selected-groups.updated',
                ],
                'extraLimitor' => [
                    'organization_id' => auth()->user()->organization_id,
                ],
            ])

            @error('groups_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('informations.file_upload') }}
            </label>

            {{-- <x-file-upload :file="$file" file-model="file"></x-file-upload> --}}
            {{-- @include('components.file-upload') --}}
            <x-file-attachment
                mode="attachment"
                ext="allDocs"
                :file="$file"
                wire:model="file"
            />

            @error('file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                {{ trans('informations.add') }}
            </button>
        </div>
    </form>
</div>
