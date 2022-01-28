<x-jet-modal wire:model.defer="showModal" max-width="5xl" mainClass="fixed top-4 lg:top-36 inset-x-0 sm:p-2 md:p-4 z-50 sm:px-0 sm:flex sm:items-top sm:justify-center max-h-vhscreen custom-scrollbar overflow-x-hidden overflow-y-auto">
    <x-slot name="title">
        <b>{{$title}}</b>
    </x-slot>

    <x-slot name="content">
        <div class="p-4 max-h-notificationdrop lg:max-h-96 custom-scrollbar overflow-x-hidden overflow-y-auto">
            <div class="flex-grow">
                <div class="flex flex-wrap justify-center lg:justify-start lg:items-start -mx-2">
                    @foreach($contacts as $contact)
                        <div class="p-2">
                            <div
                                class="border-2 border-gray-300 rounded-lg p-5 hover:bg-gray-100 cursor-pointer"
                                wire:click="logCollection('{{$contact->name}}')"
                            >
                                <div class="w-32 h-32 flex items-center">
                                    <span
                                        x-data="window.userAvatarFunc('{{ $contact->avatar_url }}', 'user-avatar')"
                                        x-on:user-image-update.window="onUserAvatarUpdate($event)"
                                        x-init="onInit()"
                                        class="w-full h-full"
                                    >
                                        <img x-show="visibleAvatar" :src="visibleAvatar" alt="{{ $contact->name }}" class="w-full h-full rounded-full">
                                    </span>
                                </div>
                                <div class="pt-2 flex items-center text-gray-900 justify-center">
                                    {{$contact->name}}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="p-2">
                        <div
                            class="border-2 border-gray-300 sm:rounded-lg p-5 hover:bg-gray-100 cursor-pointer"
                            wire:click="showOtherModal"
                        >
                            <div class="w-32 h-32 flex justify-center items-center rounded-full bg-gray-400">
                                <h1 class="text-white text-7xl xl:text-7xl font-bold ">?</h1>
                            </div>
                            <div class="pt-2 flex items-center text-gray-900 justify-center">
                                {{trans('dashboard.present_modal_other_collection_title')}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="text-right">
            <button
                wire:click="close"
                class="inline-flex justify-center px-7 py-1 text-sm font-medium text-white bg-indigo-400 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-600 focus:shadow-outline-blue active:bg-indigo-600 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
            >{{trans('contacts.cancel')}}</button>
        </div>
    </x-slot>
</x-jet-modal>
