<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <h2 class="text-lg mt-0 mb-4">{{ $data['mainTitle'] }}</h2>

    <form wire:submit.prevent="addGroup">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('groups.name') }}
            </label>

            <div class="mt-1 rounded-md shadow-sm">
                <input wire:model.defer="name" id="name" name="name" type="text" required autocomplete="off" class="text-field @error('name') error @enderror" />
            </div>

            @error('name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <label for="organization_id" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('groups.organization') }}
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
                        ]
                    ])
                </div>
            @else
                <div class="mt-1 rounded-md shadow-sm">
                    <span class="text-field">{{ $orgName }}</span>
                </div>
            @endif

            @error('organization_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                {{ trans('groups.add') }}
            </button>
        </div>
    </form>
</div>
