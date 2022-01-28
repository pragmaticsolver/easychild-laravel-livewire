<div x-on:delete-information.window="@this.call('deleteInformation', $event.detail)">
    @if (auth()->user()->isManager())
        <div class="text-right mb-3">
            <a href="{{ route('informations.create') }}" class="inline-flex items-center p-3 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition ease-in-out duration-150">
                <x-heroicon-o-plus class="-ml-0.5 mr-2 h-4 w-4" />
                {{ trans('informations.add_new') }}
            </a>
        </div>
    @endif

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 overflow-x-auto">
            <div class="align-middle inline-block overflow-hidden min-w-full shadow sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('informations.title_field') }}
                            </th>

                            @if (auth()->user()->isManager())
                                <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                    {{ trans('informations.role') }}
                                </th>
                            @endif

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('informations.date_of_creation') }}
                            </th>

                            <th class="px-6 py-3 w-12 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($informations->count())
                        <tbody class="bg-white">
                            @foreach($informations as $information)
                                <tr wire:key="{{ $information->uuid }}">
                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <button
                                            wire:click.prevent="$emitTo('components.file-modal', 'file-model-open', {{ json_encode($information->file_object) }})"
                                            class="group inline-flex space-x-2 truncate text-sm leading-5 focus:outline-none"
                                        >
                                            <x-pdf-svg class="{{ $information->last_notification ? ' text-black group-hover:text-gray-700' : ' text-red-600  group-hover:text-red-800' }}"></x-pdf-svg>

                                            <p class="truncate transition ease-in-out duration-150 {{ $information->last_notification ? ' text-black group-hover:text-gray-700' : ' text-red-600 group-hover:text-red-800' }}">
                                                {{ $information->title }}
                                            </p>
                                        </button>
                                    </td>

                                    @if (auth()->user()->isManager())
                                        <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                            <div class="text-sm leading-5 text-gray-500">
                                                @foreach($information->roles as $role)
                                                    {{ trans('extras.role_' . Str::lower($role)) }} {{ $loop->last ? '' : ', ' }}
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 text-gray-500">
                                            {{ $information->created_at->format(config('setting.format.date')) }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 w-12 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center space-x-3">
                                            <a
                                                title="{{ trans('informations.view') }}" href="#"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                wire:click.prevent="$emitTo('components.file-modal', 'file-model-open', {{ json_encode($information->file_object) }})"
                                            >
                                                <x-heroicon-o-eye class="w-5 h-5"></x-heroicon-o-eye>
                                            </a>

                                            <a
                                                title="{{ trans('informations.download') }}" href="#"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                wire:click.prevent="download('{{ $information->uuid }}')"
                                            >
                                                <x-heroicon-o-download class="w-5 h-5"></x-heroicon-o-download>
                                            </a>

                                            @if (auth()->user()->isManager())
                                                <a
                                                    title="{{ trans('informations.delete') }}" href="#"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    x-on:click.prevent="$dispatch('information-delete-confirm-modal-open', {
                                                        'title': '{{ trans('informations.delete_confirm_title') }}',
                                                        'description': '{{ trans('informations.delete_confirm_description') }}',
                                                        'event': 'delete-information',
                                                        'uuid': '{{ $information->uuid }}',
                                                    })"
                                                >
                                                    <x-heroicon-o-trash class="w-5 h-5"></x-heroicon-o-trash>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if(! $informations->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('informations.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $informations->links() }}
    </div>

    <x-confirm-modal confirm-id="information-delete"></x-confirm-modal>

    @livewire('components.file-modal')
</div>
