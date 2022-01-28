@props([
    'uploadedFiles' => [],
    'uuid' => '',
])

<div>
    @foreach($uploadedFiles as $file)
        <div wire:key="{{ $uuid . '-' . $file['name'] }}" class="py-3 flex {{ $loop->last ? '' : 'border-b border-gray-200' }}">
            <div class="w-16 mr-4 flex-shrink-0 shadow-xs rounded-lg" >
                @if($file['type'] == 'image')
                    <div class="relative pb-16 overflow-hidden rounded-lg border border-gray-100">
                        <img src="{{ $file['url'] }}" class="w-full h-full absolute object-cover rounded-lg">
                    </div>
                @else
                    <div class="w-16 h-16 bg-gray-100 text-blue-500 flex items-center justify-center rounded-lg border border-gray-100">
                        <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                @endif
            </div>

            <div>
                <div class="text-sm font-medium truncate w-40 md:w-auto">{{ $file['name'] }}</div>

                <div class="text-xs text-gray-500 uppercase">{{ $file['type'] }}</div>

                <button
                    wire:key="remove-attachment-{{ $uuid }}-{{ $file['name'] }}"
                    type="button"
                    x-on:click.prevent="$dispatch('uploaded-file-delete-confirm-modal-open', {
                        'title': '{{ trans("calendar-events.file.delete_confirm_title") }}',
                        'description': '{{ trans("calendar-events.file.delete_confirm_description") }}',
                        'event': 'remove-file-from-event',
                        'uuid': '{{ $file['name'] }}',
                    })"
                    class="text-xs text-red-500 appearance-none hover:underline"
                >
                    {{ trans('components.file_uploader.remove') }}
                </button>
            </div>
        </div>
    @endforeach

    <x-confirm-modal confirm-id="uploaded-file-delete"></x-confirm-modal>
</div>
