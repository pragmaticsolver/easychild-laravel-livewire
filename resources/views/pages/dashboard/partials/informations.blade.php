@php
    $width = 'lg:w-1/3';

    if (auth()->user()->isPrincipal()) {
        $width = 'xl:w-1/3';
    }
@endphp

@if (auth()->user()->hasAccessToService('informations'))
    <div class="mb-8 px-2 w-full md:w-1/2  {{ $width }}">
        <h2 class="mb-2 text-lg leading-6 font-medium text-gray-900">
            {{ trans('dashboard.informations_title') }}
        </h2>

        <div class="bg-white overflow-hidden shadow-md border-t border-gray-200 rounded-lg">
            <ul class="px-4 py-6 space-y-2" style="min-height: 212px;">
                @forelse($informations as $information)
                    <li class="flex items-center justify-start">
                        <div class="mr-2">
                            <x-pdf-svg class="text-black group-hover:text-gray-700"></x-pdf-svg>
                        </div>

                        <span class="font-bold pr-2 flex-1">{{ $information->title }}</span>

                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        <span class="ml-2 text-sm whitespace-no-wrap">{{ $information->created_at->format(config('setting.format.date')) }}</span>
                    </li>
                @empty
                    <li>
                        <span>{{ trans('pagination.not_found', ['type' => trans('informations.title_lower')]) }}</span>
                    </li>
                @endforelse
            </ul>

            <div class="bg-gray-200 px-4 py-4 sm:px-6">
                <div class="text-sm leading-5">
                    <a href="{{ route('informations.index') }}"
                        class="font-medium text-indigo-600 hover:text-indigo-500 transition ease-in-out duration-150">
                        {{ trans('dashboard.informations_link') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
