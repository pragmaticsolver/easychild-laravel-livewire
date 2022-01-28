@props([
    'type' => 'button',
    'disabled' => false,
    'extraClass' => 'justify-between',
    'colorClass' => '',
    'notEditable' => false,
    'alpineVar' => 'state',
])

<button
    {{ $attributes->merge(['class' => 'inline-flex items-center border-transparent rounded-full p-1.5 sm:p-2 focus:outline-none text-white ' . $extraClass . ' ' . $colorClass ]) }}
    {{ $disabled || $notEditable ? 'disabled ' : '' }}
    type="button"
    x-on:click.prevent="{{ $alpineVar }} = !{{ $alpineVar }};"
    x-bind:class="{
        'bg-green-400': {{ $alpineVar }},
        'bg-red-500': !{{ $alpineVar }},
        'cursor-pointer': !(Boolean('{{ $disabled }}') || false) && !(Boolean('{{ $notEditable }}') || false),
        'cursor-default': (Boolean('{{ $disabled }}') || false) || (Boolean('{{ $notEditable }}') || false),
        'opacity-50': (Boolean('{{ $disabled }}') || false) || (Boolean('{{ $notEditable }}') || false),
    }"
>
    {{ $slot }}
</button>
