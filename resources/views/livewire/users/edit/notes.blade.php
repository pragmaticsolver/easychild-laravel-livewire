<div
    class="sm:mx-auto sm:w-full sm:max-w-4xl"
    x-on:delete-user-note.window="@this.call('deleteNote', $event.detail)"
>
    <div class="flex items-center justify-between pb-4">
        <h1 class="text-lg leading-6 font-semibold text-gray-900 mb-2">
            {{ trans('users.sub-nav.notes') }}
        </h1>

        <button
            type="button"
            wire:click.prevent="createNew"
            class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
        >
            {{ trans('notes.add_btn') }}
        </button>
    </div>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8 overflow-x-auto">
            <div class="align-middle inline-block overflow-hidden min-w-full shadow sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('notes.title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('notes.created_at') }}
                            </th>


                            <th width="120" class="px-6 py-3 border-b border-gray-200 bg-gray-100"></th>
                        </tr>
                    </thead>

                    @if($notes->count())
                        <tbody class="bg-white">
                            @foreach($notes as $note)
                                <tr class="align-top" wire:key="user-{{ $note->id }}">
                                    <td class="px-6 py-4 border-b border-gray-200">
                                        <div class="mb-2 leading-5 font-medium text-gray-900 flex items-center">
                                            <strong class="pr-4">
                                                {{ $note->title }}
                                            </strong>

                                            <span
                                                x-data="{priority: '{{ $note->priority }}'}"
                                                class="text-xs px-2 py-0.5 rounded-md"
                                                :class="{
                                                    'bg-green-300 text-white': priority == 'low',
                                                    'bg-orange-300 text-black': priority == 'normal',
                                                    'bg-red-300 text-white': priority == 'urgent',
                                                }"
                                            >
                                                {{ $note->priority }}
                                            </span>
                                        </div>
                                        <div class="leading-5 text-gray-500">
                                            {!! nl2br($note->text) !!}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap border-b border-gray-200">
                                        <div class="text-sm leading-5 text-gray-500">{{ $note->created_at->format(config('setting.format.date')) }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-no-wrap text-right border-b border-gray-200 text-sm leading-5 font-medium">
                                        <div class="flex items-center">
                                            <a title="{{ trans('notes.edit') }}" wire:click.prevent="edit({{ $note->id }})" href="#" class="text-indigo-600 hover:text-indigo-900">
                                                <x-heroicon-o-pencil class="w-5 h-5"></x-heroicon-o-pencil>
                                            </a>

                                            @if (! auth()->user()->isPrincipal())
                                                <a
                                                    title="{{ trans('notes.delete_top_title') }}" href="#"
                                                    class="text-indigo-600 hover:text-indigo-900 ml-3"
                                                    x-on:click.prevent="$dispatch('user-note-delete-confirm-modal-open', {
                                                        'title': '{{ trans("notes.delete_top_title") }}',
                                                        'description': '{{ trans("notes.delete_description", ['title' => $note->title]) }}',
                                                        'event': 'delete-user-note',
                                                        'uuid': '{{ $note->id }}',
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

    @if(! $notes->count())
        <x-no-data-found>
            {{ trans('notes.no_items') }}
        </x-no-data-found>
    @endif

    <div>
        {{ $notes->links() }}
    </div>

    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model.defer="showModal">
            <x-slot name="title">{{ trans('notes.add_modal_title') }}</x-slot>

            <x-slot name="content">
                <div class="mt-4">
                    <label for="note.title" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('notes.title') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <input wire:model.defer="note.title" id="note.title" name="note.title" type="text" required autocomplete="off" class="text-field @error('note.title') error @enderror" />
                    </div>

                    @error('note.title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="note.text" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('notes.text') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <textarea wire:model.defer="note.text" id="note.text" name="note.text" row="7" class="text-field min-h-40 resize-none @error('note.text') error @enderror"></textarea>
                    </div>

                    @error('note.text')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="note.priority" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('notes.priority_title') }}
                    </label>

                    <div class="mt-1 rounded-md shadow-sm">
                        <select wire:model.defer="note.priority" id="note.priority" name="note.priority" required class="form-select select-field @error('note.priority') error @enderror">
                            @foreach($this->priorities as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>

                    @error('note.priority')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('notes.submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="$set('showModal', false)"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('notes.cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>

    <x-confirm-modal confirm-id="user-note-delete"></x-confirm-modal>
</div>
