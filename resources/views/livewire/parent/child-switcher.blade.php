<div class="flex space-x-2"
    x-data="{
        currentUuid: '{{ $current->uuid }}',
        switchTheChild(uuid) {
            if (uuid === this.currentUuid) {
                return;
            }

            @this.call('switchChild', uuid)
        }
    }"
>
    @foreach($children as $child)
        <div wire:key="{{ $child->uuid }}"
            class="inline-flex items-center rounded-full p-px"
            x-bind:class="{
                'bg-indigo-600': '{{ $child->uuid }}' === currentUuid,
                'bg-gray-400': '{{ $child->uuid }}' !== currentUuid,
            }"
        >
            <div class="flex-shrink-0 w-9 m-px">
                <button
                    type="button"
                    class="focus:outline-none block"
                    x-on:click.prevent="switchTheChild('{{ $child->uuid }}');"
                >
                    <img src="{{ $child->avatar_url }}" class="rounded-full align-top w-9 h-9" alt="{{ $child->full_name }}">
                </button>
            </div>

            <div
                class="text-white transition-all delay-100 font-semibold"
                x-bind:class="{
                    'ml-2 flex-1 pr-3': '{{ $child->uuid }}' === currentUuid,
                    'w-0 overflow-hidden': '{{ $child->uuid }}' !== currentUuid
                }"
            >
                {{ $child->given_names }}
            </div>
        </div>
    @endforeach
</div>
