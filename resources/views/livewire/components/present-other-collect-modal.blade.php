<form wire:submit.prevent="submit">
    <x-jet-modal wire:model.defer="showModal" max-width="2xl" mainClass="fixed top-40 lg:top-72 inset-x-0 sm:p-2 md:p-4 z-50 sm:px-0 sm:flex sm:items-top sm:justify-center max-h-vhscreen custom-scrollbar overflow-x-hidden overflow-y-auto">
        <x-slot name="title">
            <b>{{$title}}</b>
        </x-slot>

        <x-slot name="content">
            <div class="p-4 max-h-notificationdrop lg:max-h-96 custom-scrollbar overflow-x-hidden overflow-y-auto">
                <div class="mt-1 rounded-md shadow-sm">
                    <input wire:model.defer="name" id="name" name="name" type="text" required autocomplete="off" class="text-field @error('name') error @enderror" />

                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-slot>
        <x-slot name="footer">
            <div class="text-right">
                <button
                    type="button"
                    wire:click.prevent="close"
                    class="inline-flex justify-center mr-1 px-7 py-1 text-sm font-medium text-white bg-indigo-400 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-600 focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                >{{trans('contacts.cancel')}}</button>

                <button
                    type="submit"
                    class="inline-flex justify-center px-9 py-1 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:border-indigo-800 focus:shadow-outline-blue active:bg-indigo-800 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                >{{trans('contacts.submit')}}</button>
            </div>
        </x-slot>
    </x-jet-modal>
</form>
