@props([
    'sortBy',
    'name',
    'order' => 'ASC',
    'text',
])

<a href="#" class="hover:text-gray-400 inline-flex items-center" wire:click.prevent="changeSort('{{ $name }}')">
    <span class="py-1">
        {{ $text }}
    </span>

    @if ($sortBy == $name)
        @if ($order === 'ASC')
            <x-heroicon-o-sort-ascending class="ml-2 w-4 h-4"></x-heroicon-o-sort-ascending>
        @else
            <x-heroicon-o-sort-descending class="ml-2 w-4 h-4"></x-heroicon-o-sort-descending>
        @endif
    @endif
</a>
