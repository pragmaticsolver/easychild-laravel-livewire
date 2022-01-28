@props([
    'ext' => 'pdf',
    'maxSize' => 10,
    'fileModel' => 'file',
    'file',
])

@php
    $modelId = 'model-id-';
    $modelId .= Str::random(5);

    $extension = [
        'pdf' => 'application/pdf',
    ][$ext];
@endphp

<div class="mt-2 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md relative">
    <input wire:model="{{ $fileModel }}" accept="{{ $extension }}" type="file" id="{{ $modelId }}" class="opacity-0 z-10 absolute left-0 top-0 w-full h-full">

    <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" focusable="false" data-prefix="fal" data-icon="file-pdf" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M369.9 97.9L286 14C277 5 264.8-.1 252.1-.1H48C21.5 0 0 21.5 0 48v416c0 26.5 21.5 48 48 48h288c26.5 0 48-21.5 48-48V131.9c0-12.7-5.1-25-14.1-34zm-22.6 22.7c2.1 2.1 3.5 4.6 4.2 7.4H256V32.5c2.8.7 5.3 2.1 7.4 4.2l83.9 83.9zM336 480H48c-8.8 0-16-7.2-16-16V48c0-8.8 7.2-16 16-16h176v104c0 13.3 10.7 24 24 24h104v304c0 8.8-7.2 16-16 16zm-22-171.2c-13.5-13.3-55-9.2-73.7-6.7-21.2-12.8-35.2-30.4-45.1-56.6 4.3-18 12-47.2 6.4-64.9-4.4-28.1-39.7-24.7-44.6-6.8-5 18.3-.3 44.4 8.4 77.8-11.9 28.4-29.7 66.9-42.1 88.6-20.8 10.7-54.1 29.3-58.8 52.4-3.5 16.8 22.9 39.4 53.1 6.4 9.1-9.9 19.3-24.8 31.3-45.5 26.7-8.8 56.1-19.8 82-24 21.9 12 47.6 19.9 64.6 19.9 27.7.1 28.9-30.2 18.5-40.6zm-229.2 89c5.9-15.9 28.6-34.4 35.5-40.8-22.1 35.3-35.5 41.5-35.5 40.8zM180 175.5c8.7 0 7.8 37.5 2.1 47.6-5.2-16.3-5-47.6-2.1-47.6zm-28.4 159.3c11.3-19.8 21-43.2 28.8-63.7 9.7 17.7 22.1 31.7 35.1 41.5-24.3 4.7-45.4 15.1-63.9 22.2zm153.4-5.9s-5.8 7-43.5-9.1c41-3 47.7 6.4 43.5 9.1z" class=""></path></svg>

        <p class="mt-2 text-sm text-gray-600">
            <div>
                @if($file)
                    <label for="{{ $modelId }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none transition duration-150 ease-in-out cursor-pointer">
                        {{ trans('components.file_uploader.title_reset') }}
                    </label>

                    {{ trans('components.file_uploader.title_2') }}
                @endif
            </div>

            <div>
                @if (! $file)
                    <label for="{{ $modelId }}" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none transition duration-150 ease-in-out cursor-pointer">
                        {{ trans('components.file_uploader.title') }}
                    </label>

                    {{ trans('components.file_uploader.title_2') }}
                @endif
            </div>
        </p>

        <p class="mt-1 text-xs text-gray-500">
            <span>
                @if ($file)
                    <strong>
                        {{-- {{ $file->getClientOriginalName() }} --}}
                        {{ $file }}
                    </strong>
                    /
                    @if ($file->getSize() > (1024 * 1024))
                        {{ numformat($file->getSize() / (1024 * 1024), 1) }} MB
                    @else
                        {{ numformat($file->getSize() / 1024, 1) }} KB
                    @endif
                @else
                    {{ $ext }} / {{ trans('components.file_uploader.max_file_size', ['size' => $maxSize]) }}
                @endif
            </span>
        </p>
    </div>
</div>
