@props([
    'items' => [],
    'name',
])

<div
    {{ $attributes->merge(['class' => 'bg-white rounded-md -space-y-px']) }}
    x-data="{
        value: @entangle($attributes->wire('model')),
    }"
>
    <!-- Checked: "bg-indigo-50 border-indigo-200 z-10", Not Checked: "border-gray-200" -->

    @foreach($items as $item)
        <label
            class="relative border p-4 flex cursor-pointer {{ $loop->first ? ' rounded-t-md' : '' }}  {{ $loop->last ? ' rounded-b-md' : '' }}"
            :class="{
                'bg-indigo-100 border-indigo-200 z-10': value == '{{ $item }}',
                'border-gray-200': value != '{{ $item }}'
            }"
        >
            <input type="radio" name="{{ $name }}" x-model="value" value="{{ $item }}" class="h-4 w-4 mt-0.5 cursor-pointer text-indigo-600 border-gray-300 focus:ring-indigo-500">

            <div class="ml-3 flex flex-col">
                <!-- Checked: "text-indigo-900", Not Checked: "text-gray-900" -->
                <span
                    class="block text-sm font-medium"
                    x-bind:class="{'text-indigo-900': value == '{{ $item }}', 'text-gray-900': value != '{{ $item }}'}"
                >
                    {{ trans('schedules.check_out.execuses.' . $item) }}
                </span>
            </div>
        </label>
    @endforeach
    <span class="text-red-600 text-sm" x-bind:class="{'hidden': value != 'ill'}">{{trans('schedules.notification.hint_ill_case')}}</span>
</div>
