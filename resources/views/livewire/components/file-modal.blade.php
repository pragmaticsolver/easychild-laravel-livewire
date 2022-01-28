<x-jet-modal wire:model.defer="showModal" max-width="5xl">
    @if ($file)
        <x-slot name="title">
            {{ $file['name'] }}
        </x-slot>
    @endif

    <x-slot name="content">
        @if($file)
            @if($file['type'] == 'image')
                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="max-w-full h-auto block mx-auto">
            @else
                <div class="relative h-pdfmodal sm:h-pdfmodel-sm md:h-pdfmodel-md">
                    <iframe class="w-full h-full absolute inset-0" src="{{ route('pdf-viewer', ['file' => $file['url']]) }}" frameborder="0" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            @endif
        @endif
    </x-slot>
</x-jet-modal>
