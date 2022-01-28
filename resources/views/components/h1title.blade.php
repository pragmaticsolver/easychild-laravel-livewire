@props([
    'navLinks' => [],
    'pageTitle' => '',
    'showChildSwitcher' => false,
])

<header class="bg-white shadow-sm">
    <div class="flex flex-wrap items-center max-w-7xl mx-auto px-4 py-1.5">
        <h1 class="text-lg leading-6 font-semibold text-gray-900 py-3 mr-4">
            {{ $pageTitle }}
        </h1>

        @if($navLinks && count($navLinks))
            <div class="flex flex-wrap items-baseline -mx-1">
                @foreach($navLinks as $linkItem)
                    <div class="p-1">
                        <x-navlink class="block" :has-active="true" :active="$linkItem['active']" :href="$linkItem['href']" text-class="text-black" active-class="bg-gray-300" hover-class="hover:bg-gray-300">
                            {{ $linkItem['text'] }}
                        </x-navlink>
                    </div>
                @endforeach
            </div>
        @endif

        @if (auth()->user()->isParent() && $showChildSwitcher)
            <div class="ml-auto">
                @livewire('parent.child-switcher')
            </div>
        @endif
    </div>
</header>
