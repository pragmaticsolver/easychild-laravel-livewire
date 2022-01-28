<div
    x-data
    x-on:show-check-out-modal.window="@this.call('open', $event.detail.date, $event.detail.cause)"
>
    <form wire:submit.prevent="submit">
        <x-jet-modal wire:model="showModal" max-width="sm" cancel-event="cancel">
            <x-slot name="title">
                {{ trans('calendar-events.add_modal_title') }}
            </x-slot>

            <x-slot name="content">
                <div>
                    <label for="cause" class="block text-sm font-medium text-gray-700 leading-5">
                        Cause
                    </label>

                    <x-group-radio
                        name="cause"
                        wire:model.defer="cause"
                        :items="$this->causes"
                        class="mt-1"
                    />

                    @error('cause')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="date" class="block text-sm font-medium text-gray-700 leading-5">
                        {{ trans('schedules.check_out.to_label') }}
                    </label>

                    <div class="relative mt-1">
                        <x-date-picker
                            name="date"
                            wire:model.defer="end"
                            class="@error('end') error @enderror"
                        ></x-date-picker>
                    </div>

                    @error('date')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </x-slot>

            <x-slot name="footer">
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('calendar-events.submit') }}
                    </button>

                    <button
                        type="button"
                        wire:click.prevent="cancel"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        {{ trans('calendar-events.cancel') }}
                    </button>
                </div>
            </x-slot>
        </x-jet-modal>
    </form>
</div>
