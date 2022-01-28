<x-navlink
    {{-- class="relative flex sm:block" --}}
    class="relative block w-full md:w-auto text-left mt-2 md:mt-0 md:text-center"
    href="{{ route('informations.index') }}"
>
    {{ trans('informations.index_title') }}

    <span class="sm:absolute ml-auto left-full bottom-full sm:-mb-3 sm:-ml-2 py-0.5 px-1 text-xs leading-4 rounded-full bg-red-500 text-white transition ease-in-out duration-150 text-center" style="min-width: 20px; {{ $number == 0 ? ' display: none;' : '' }}">
        {{ $number }}
    </span>
</x-navlink>
