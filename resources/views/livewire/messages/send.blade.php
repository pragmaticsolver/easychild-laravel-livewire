@php
    $formData = [
        'form' => [
            'message' => $message,
            'file' => $file
        ],
    ];
@endphp
<div>
    <form
        {{--        x-on:submit.prevent="@this.call('sendMessage', form); setTimeout(function() {form.message = null;}, 10)"--}}
        {{--        x-data="{{ json_encode($formData) }}"--}}
        wire:submit.prevent="sendMessage"
        class="relative"

    >
        <button type="button" class="left-2 @if($message && strlen($message) > 1600) top-13 @else top-3 @endif absolute text-indigo-600" id="btn_attachment">
            <x-heroicon-o-paper-clip class="w-5 h-5" />
        </button>
        <input type="file" name="file" wire:model="file" id="attachment" class="hidden"/>
        @if($message && strlen($message) > 1600)
            <div
                class="p-3 text-xs text-red-600"
                {{--            wire:ignore x-show="form.message && form.message.length > 1600"--}}
            >
                {{ trans('validation.max.string', ['attribute' => trans('messages.title'), 'max' => 1600]) }}
            </div>
        @endif


        @error('file')
            <div
                class="p-3 text-xs text-red-600"
                {{--            wire:ignore x-show="form.message && form.message.length > 1600"--}}
            >
                {{trans('messages.message_send.file_size_exceed')}}
            </div>
        @enderror


        <textarea
            id="msg_text"
            class="text-field h-28 custom-scrollbar pl-10 pr-8 border-none resize-none"
            {{--            x-model="form.message"--}}
            wire:model="message"
            placeholder="{{ trans('messages.send_message_placeholder') }}"
        ></textarea>

        <button
            {{--            x-bind:disabled="form.message && form.message.length > 1600"--}}
            type="submit"
            class="right-2 @if($message && strlen($message) > 1600) top-13 cursor-default opacity-50 @else top-3 @endif absolute text-indigo-600"
            {{--            x-bind:class="{--}}
            {{--            'cursor-default opacity-50': form.message && form.message.length > 1600--}}
            {{--            }"--}}
            @if($message && strlen($message) > 1600) disabled @endif
        >
            <x-zondicon-send class="w-5 h-5" />
        </button>
    </form>
    <script>
        document.getElementById('btn_attachment').addEventListener('click', function () {
            document.getElementById('attachment').click();
        })

        document.getElementById('attachment').addEventListener('change', function () {
            console.log("this.file[0].name", this.files[0].name);
            document.getElementById('msg_text').value = this.files[0].name;
            Livewire.emit('change-file', this.files[0].name);

        })
    </script>
</div>

