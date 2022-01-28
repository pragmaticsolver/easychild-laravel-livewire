<div class="sm:mx-auto sm:w-full sm:max-w-md">
    <form wire:submit.prevent="import">
        <div>
            <label for="organization_id" class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('users.organization') }}
            </label>

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

            @error('organization_id')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-5">
            <label class="block text-sm font-medium text-gray-700 leading-5">
                {{ trans('informations.file_upload') }}
            </label>

            <x-file-attachment
                mode="attachment"
                ext="excel"
                :file="$excelFile"
                wire:model="excelFile"
            />

            @error('excelFile')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mt-6">
            <button
                class="flex mb-2 justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
                wire:target="import"
            >
                {{ trans('informations.add') }}
            </button>
        </div>
    </form>
</div>
