<div>
    <header class="bg-white shadow-sm">
        <div class="flex flex-wrap items-center max-w-7xl mx-auto px-4 py-1.5 border border-transparent">
            <div class="flex-1 mr-3">
                <div class="flex flex-wrap items-baseline -mx-1">
                    <h1 class="text-lg font-semibold leading-6 text-gray-900 py-3 mr-4">
                        {{ trans('reports.title') }}
                    </h1>
                    @foreach($this->viewType as $type => $item)
                        <div class=" p-1">
                            <x-navlink class="block" :has-active="true" :active="$view == $type" :href="$item['href']" text-class="text-black" active-class="bg-gray-300" hover-class="hover:bg-gray-300">
                                {{$item['text']}}
                            </x-navlink>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <span>
                    @if($this->canShowDownloadButton())
                        <x-pdf-download class="mr-3" />
                    @endif
                </span>
            </div>
        </div>
    </header>

    <div>
        @if($view == 'absence')
            @livewire('reports.absence')
        @else
            @livewire('reports.planning')
        @endif
    </div>
</div>
