@php
    $statusTypes = [
        'available' => false,
    ];
@endphp

<x-switch :disabled="false" wire:model="available"></x-switch>