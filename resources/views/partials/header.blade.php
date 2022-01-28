@php
    $currentGroup = '';
    $principalGroups = collect();

    if (auth()->user()->isPrincipal() && auth()->user()->principal_current_group) {
        $principalGroups = auth()->user()->groups;
        $currentPrincipalGroup = auth()->user()->principal_current_group;
        $currentGroup = $currentPrincipalGroup->name;
        $currentGroupUuid = $currentPrincipalGroup->uuid;
    }

    $currentRole = (string) Str::of(auth()->user()->role)->lower();
@endphp

<nav
    class="bg-gray-800"
    x-data="{mobileMenuOpen: false}"
    x-on:click.away="mobileMenuOpen = false"
    x-on:principal-switch-group.window="mobileMenuOpen = false"
>
    <div class="max-w-7xl mx-auto px-4 relative">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a
                        href="{{ route('dashboard') }}"
                        x-data="window.userAvatarFunc('{{ auth()->user()->organization_avatar }}', 'org-avatar')"
                        x-on:org-image-update.window="onUserAvatarUpdate($event)"
                        x-init="onInit()"
                    >
                        <img :src="visibleAvatar" alt="{{ auth()->user()->organization ? auth()->user()->organization->name : 'Org Avatar' }}" class="h-10 w-10">
                    </a>
                </div>

                <div x-show="mobileMenuOpen" class="lg:flex-imp absolute z-30 lg:z-0 top-full left-0 w-full lg:static">
                    <div class="bg-gray-800 lg:bg-none px-3 py-3 lg:px-0 lg:py-3 lg:ml-6 flex flex-col lg:flex-row items-baseline lg:space-x-2">
                        @if (auth()->user()->isAdmin())
                            <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('organizations.index') }}">{{ trans('nav.organizations') }}</x-navlink>
                        @endif

                        @if (! (auth()->user()->isAdmin() || auth()->user()->isVendor() || auth()->user()->isContractor()))
                            <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('schedules.index') }}">{{ trans('nav.schedules') }}</x-navlink>

                            @if (auth()->user()->isManagerOrPrincipal())
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('presence') }}">{{ trans('nav.presence') }}</x-navlink>
                            @endif

                            @if (auth()->user()->hasAccessToService('messages'))
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('messages.index') }}">{{ trans('nav.messages') }}</x-navlink>
                            @endif
                        @endif

                        @if (! auth()->user()->isAdmin())
                            @if (auth()->user()->hasAccessToService('informations'))
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('informations.index') }}">{{ trans('informations.index_title') }}</x-navlink>
                            @endif
                        @endif

                        @if (!auth()->user()->isContractor())
                            @if (! (auth()->user()->isVendor() || auth()->user()->isUser() || auth()->user()->isParent()))
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('users.index') }}">{{ trans('nav.users') }}</x-navlink>
                            @endif

                            @if (auth()->user()->isManager())
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('organizations.profile') }}">{{ trans('organizations.profile_title') }}</x-navlink>
                            @endif

                            @if (auth()->user()->isManager() || auth()->user()->isPrincipal())
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('reports.index') }}">{{ trans('nav.reports') }}</x-navlink>
                            @endif

                            @if (! auth()->user()->isAdmin())
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('calendar') }}">{{ trans('nav.calendar') }}</x-navlink>
                            @endif

                            @if (auth()->user()->isAdmin())
                                <x-navlink class="block w-full lg:w-auto text-left mt-2 lg:mt-0 lg:text-center" href="{{ route('import.children') }}">{{ trans('nav.import') }}</x-navlink>
                            @endif
                        @endif
                    </div>

                    <div class="bg-gray-800 lg:hidden pt-4 pb-3 border-t border-gray-700">
                        <div class="flex items-center px-5">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full overflow-hidden text-white flex items-center justify-center">
                                    <span
                                        x-data="window.userAvatarFunc('{{ auth()->user()->avatar_url }}', 'user-avatar')"
                                        x-on:user-image-update.window="onUserAvatarUpdate($event)"
                                        x-init="onInit()"
                                    >
                                        <img x-show="visibleAvatar" :src="visibleAvatar" alt="{{ auth()->user()->full_name }}" class="h-10 w-10">

                                        <svg x-show="!visibleAvatar" class="h-10 w-10 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="ml-3">
                                @if(auth()->user()->role == 'Vendor')
                                    <div class="text-sm leading-none text-white">{{ auth()->user()->last_name }}</div>
                                @else
                                    <div class="text-sm leading-none text-white">{{ auth()->user()->given_names }}</div>
                                @endif

                                <div class="mt-1 text-xs leading-none text-gray-400">
                                    {{ trans("nav.{$currentRole}") }}
                                </div>

                                @if(auth()->user()->isPrincipal())
                                    @php
                                        $currentGroup = '';

                                        if (auth()->user()->principal_current_group) {
                                            $currentGroup = auth()->user()->principal_current_group->name;
                                        }
                                    @endphp

                                    <div
                                        class="mt-1 text-xs leading-none text-gray-400"
                                        x-data="{
                                            'currentGroup': '{{ $currentGroup }}',
                                        }"
                                        x-on:principal-switch-group.window="currentGroup = $event.detail"
                                    >
                                        <span class="text-white" x-text="currentGroup"></span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3 px-2" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">
                            <x-navlink class="block text-left" href="{{ route('users.profile') }}">{{ trans('nav.profile') }}</x-navlink>

                            @if (auth()->user()->isAdmin())
                                <x-navlink class="block text-left" href="{{ route('translation.update') }}">{{ trans('nav.translations') }}</x-navlink>
                            @endif

                            @if(auth()->user()->isPrincipal())
                                <x-dropdown :not-absolute="true">
                                    <x-slot name="button">
                                        <button
                                            type="button"
                                            x-on:click="dropOpen = !dropOpen"
                                            class="w-full px-3 py-2 mt-2 rounded-md text-sm font-medium focus:outline-none text-white hover:bg-gray-700 block text-left"
                                            x-on:principal-switch-group.window="dropOpen = false"
                                        >
                                            {{ trans('nav.switch_group') }}
                                            <span
                                                class="pl-2"
                                                x-data="{currentGroup: '{{ $currentGroup }}'}"
                                                x-text="`(${currentGroup})`"
                                                x-on:principal-switch-group.window="currentGroup = $event.detail"
                                            ></span>
                                        </button>
                                    </x-slot>

                                    <div
                                        class="py-1 rounded-md bg-white shadow-xs text-left"
                                        x-data="{
                                            currentGroup: '{{ $currentGroup }}',
                                            currentGroupId: '{{ $currentGroupUuid }}',
                                        }"
                                        x-on:principal-switch-group.window="currentGroup = $event.detail"
                                        x-init="$watch('currentGroupId', val => window.livewire.emitTo('components.switch-principal-group', 'switchPrincipalGroup', val))"
                                    >
                                        @foreach($principalGroups as $principalGroup)
                                            <a
                                                wire:key="{{ $principalGroup->uuid }}"
                                                href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-300"
                                                :class="{
                                                    'bg-gray-300': currentGroupId == '{{ $principalGroup->uuid }}'
                                                }"
                                                x-on:click.prevent="currentGroupId = '{{ $principalGroup->uuid }}'"
                                            >
                                                {{ $principalGroup->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                </x-dropdown>
                            @endif

                            {{-- @livewire('components.switch-user') --}}

                            <button
                                type="button"
                                class="mt-2 block w-full px-3 py-2 text-left rounded-md text-sm font-medium text-white focus:outline-none hover:bg-gray-700"
                                role="menuitem"
                                x-on:click.prevent="$dispatch('confirm-user-logout', {
                                    'title': '{{ trans('extras.logout_user_confirm_title') }}',
                                    'description': '{{ trans('extras.logout_user_confirm_description') }}',
                                    'event': 'logout-user',
                                })"
                            >
                                {{ trans('nav.sign_out') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center">
                <div
                    class="inline-flex items-center"
                    x-data="{
                        unreadNotifications: 0,
                    }"
                    x-on:update-unread-notifications-count.window="unreadNotifications = $event.detail"
                >
                    <button
                        class="relative text-white hover:text-gray-400 outline-none hover:outline-none focus:outline-none"
                        type="button"
                        x-on:click.prevent="$dispatch('toggle-user-notification-drop')"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>

                        <span class="min-w-5 -mb-3 -mr-2 absolute right-full bottom-full py-0.5 px-1 text-xs leading-4 rounded-full bg-red-500 text-white transition ease-in-out duration-150 text-center" style="display: none;" x-show="unreadNotifications && unreadNotifications > 0" x-text="unreadNotifications"></span>
                    </button>
                </div>

                <div class="hidden lg:block">
                    <div class="ml-3 flex items-center">
                        @if(auth()->user()->isPrincipal())
                            <x-dropdown class="mr-3">
                                <x-slot name="button">
                                    <button
                                        type="button"
                                        x-on:click="dropOpen = !dropOpen"
                                        class="text-sm xl:text-base leading-none text-gray-400 whitespace-no-wrap outline-none focus:outline-none"
                                        x-on:principal-switch-group.window="dropOpen = false"
                                    >
                                        <span
                                            x-data="{currentGroup: '{{ $currentGroup }}'}"
                                            x-text="currentGroup"
                                            x-on:principal-switch-group.window="currentGroup = $event.detail"
                                        ></span>
                                    </button>
                                </x-slot>

                                <div
                                    class="py-1 rounded-md bg-white shadow-xs text-left"
                                    x-data="{
                                        currentGroup: '{{ $currentGroup }}',
                                        currentGroupId: '{{ $currentGroupUuid }}',
                                    }"
                                    x-on:principal-switch-group.window="currentGroup = $event.detail"
                                    x-init="$watch('currentGroupId', val => window.livewire.emitTo('components.switch-principal-group', 'switchPrincipalGroup', val))"
                                >
                                    @foreach($principalGroups as $principalGroup)
                                        <a
                                            wire:key="{{ $principalGroup->uuid }}"
                                            href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-300"
                                            :class="{
                                                'bg-gray-300': currentGroupId == '{{ $principalGroup->uuid }}'
                                            }"
                                            x-on:click.prevent="currentGroupId = '{{ $principalGroup->uuid }}'"
                                        >
                                            {{ $principalGroup->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </x-dropdown>
                        @endif

                        <!-- Profile dropdown -->
                        <x-dropdown>
                            <x-slot name="button">
                                <button
                                    x-on:click="dropOpen = !dropOpen"
                                    class="overflow-hidden bg-blue-400 w-8 h-8 flex items-center text-sm rounded-full text-white focus:outline-none focus:shadow-solid"
                                    id="user-menu" aria-label="User menu" aria-haspopup="true"
                                >
                                    <span
                                        x-data="window.userAvatarFunc('{{ auth()->user()->avatar_url }}', 'user-avatar')"
                                        x-on:user-image-update.window="onUserAvatarUpdate($event)"
                                        x-init="onInit()"
                                        class="w-full h-full"
                                    >
                                        <img x-show="visibleAvatar" :src="visibleAvatar" alt="{{ auth()->user()->full_name }}" class="w-full h-full">
                                    </span>
                                </button>
                            </x-slot>

                            <div class="py-1 rounded-md bg-white shadow-xs text-left">
                                <span class="block px-4 py-2">
                                    @if(auth()->user()->role == 'Vendor')
                                        <span class="text-sm block text-gray-800">{{ auth()->user()->last_name }}</span>
                                    @else
                                        <span class="text-sm block text-gray-800">{{ auth()->user()->given_names }}</span>
                                    @endif

                                    <span class="text-xs block text-gray-400">
                                        {{ trans("nav.{$currentRole}") }}
                                    </span>
                                </span>

                                <a href="{{ route('users.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ trans('nav.profile') }}
                                </a>

                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('translation.update') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ trans('nav.translations') }}
                                    </a>
                                @endif

                                {{-- @livewire('components.switch-user') --}}

                                @if (auth()->user()->isImpersonated())
                                    <button
                                        type="button"
                                        class="block focus:outline-none text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        role="menuitem"
                                        x-on:click.prevent="$dispatch('impersonation-impersonate-out')"
                                    >
                                        {{ trans('impersonations.leave') }}
                                    </button>
                                @else
                                    <button
                                        {{-- type="submit" --}}
                                        type="button"
                                        class="block focus:outline-none text-left w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                        role="menuitem"
                                        x-on:click.prevent="$dispatch('confirm-user-logout', {
                                            'title': '{{ trans('extras.logout_user_confirm_title') }}',
                                            'description': '{{ trans('extras.logout_user_confirm_description') }}',
                                            'event': 'logout-user',
                                        })"
                                    >
                                        {{ trans('nav.sign_out') }}
                                    </button>
                                @endif
                            </div>
                        </x-dropdown>
                    </div>
                </div>

                <div class="ml-3 -mr-2 flex lg:hidden">
                    <!-- Mobile menu button -->
                    <button
                        x-on:click.prevent="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-white">
                        <span x-show="!mobileMenuOpen">
                            <x-heroicon-o-menu class="h-6 w-6" />
                        </span>
                        <span x-show="mobileMenuOpen">
                            <x-heroicon-o-x class="h-6 w-6" />
                        </span>
                    </button>
                </div>
            </div>

            @if (!auth()->user()->isContractor())
                @livewire('notifications.index')
            @endif
        </div>
    </div>
</nav>
