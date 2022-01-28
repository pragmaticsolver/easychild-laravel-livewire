@extends('layouts.message')

@section('pageTitle')
    <header class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 {{ auth()->user()->isParent() ? 'py-2.5' : 'py-4' }}">
            <div class="flex justify-between items-center">
                <h1 class="text-lg leading-6 font-semibold text-gray-900">
                    {{ $title }}
                </h1>

                <div x-data class="sm:hidden">
                    <button x-on:click.prevent="$dispatch('chat-threads-enable')" type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150">
                        <x-heroicon-o-chat-alt-2 class="w-6 h-6"></x-heroicon-o-chat-alt-2>
                    </button>
                </div>

                @if (auth()->user()->isParent())
                    @livewire('parent.child-switcher')
                @endif
            </div>
        </div>
    </header>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 flex w-full" style="height: calc(100vh - 120px);">
        <div class="flex border border-gray-200 rounded-lg w-full">
            @livewire('messages.sidebar', ['conversation' => $conversation])

            @livewire('messages.thread')

            @livewire('messages.create')

            @livewire('messages.read-modal')
        </div>
    </div>

    <x-confirm-modal confirm-id="conversation-message-delete"></x-confirm-modal>
@endsection
