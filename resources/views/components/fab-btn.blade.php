@props([
    'isSingle' => true,
    'singleRoute' => null,
    'mainTooltip' => trans('extras.go_back'),
])

<div
    class="fixed bottom-4 right-4 z-10"
    x-data="{fabOpen: false}"
    @if (! $isSingle)
        x-on:click.away="fabOpen = false"
    @endif
>
    <a
        @if ($isSingle && $singleRoute)
            href="{{ $singleRoute }}"
        @else
            href="#"
            x-on:click.prevent="fabOpen = true"
        @endif
        title="{{ $mainTooltip }}"
        class="flex items-center justify-center p-3 rounded-full text-white bg-blue-600 hover:bg-blue-700 outline-none focus:outline-none shadow-md"
    >
        <x-heroicon-o-arrow-left class="h-5 w-5" />
    </a>
</div>
