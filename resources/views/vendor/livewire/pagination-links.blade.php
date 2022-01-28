@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="py-3 sm:flex items-center justify-between">
        <div class="hidden sm:block">
            <p class="text-sm text-gray-700 leading-5" wire:key="paginator-{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}">
                {!!
                    trans('pagination.description', [
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'total' => $paginator->total(),
                    ])
                !!}
            </p>
        </div>

        <span class="flex justify-between sm:inline-flex sm:space-x-5">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-white bg-blue-500 border border-blue-500 cursor-default rounded-md leading-5 opacity-50" aria-hidden="true">
                        {!! __('pagination.previous') !!}
                    </span>
                </span>
            @else
                <button wire:click="previousPage" rel="prev" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 border border-gray-300 rounded-md leading-5 outline-none focus:z-10 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('pagination.previous') }}">
                    {!! __('pagination.previous') !!}
                </button>
            @endif

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button wire:click="nextPage" rel="next" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 border border-gray-300 rounded-md leading-5 outline-none focus:z-10 focus:outline-none transition ease-in-out duration-150" aria-label="{{ __('pagination.next') }}">
                    {!! __('pagination.next') !!}
                </button>
            @else
                <span aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-white bg-blue-500 border border-blue-500 cursor-default rounded-md leading-5 opacity-50" aria-hidden="true">
                        {!! __('pagination.next') !!}
                    </span>
                </span>
            @endif
        </span>
    </nav>
@endif
