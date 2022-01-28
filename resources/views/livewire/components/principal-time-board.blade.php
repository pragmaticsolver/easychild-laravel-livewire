<div class="border rounded-md shadow-md px-3 py-3">
    <div class="flex flex-wrap -mx-3">
        <div class="px-3 py-2 w-full sm:w-1/2 lg:w-full">
            <h2 class="mb-5 uppercase text-calendar-dark font-bold border-b border-calendar-dark">{{ trans('dashboard.absent_title') }}</h2>

            <ul class="space-y-4">
                @forelse($absentUsers as $item)
                    <li wire:key="{{ $item['uuid'] }}">
                        <div class="flex justify-between">
                            <strong class="font-bold pr-4">
                                <a href="{{ route('users.edit', ['user' => $item['user_uuid'], 'type' => 'contacts']) }}">
                                    {{ $item['user_name'] }}
                                </a>
                            </strong>

                            <button
                                type="button"
                                title="{{ trans('schedules.tooltip_approve') }}"
                                wire:click="setPresenceStart('{{ $item['uuid'] }}')"
                                class="inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-indigo-600 focus:outline-none mx-1 cursor-pointer hover:bg-indigo-500"
                            >
                                <x-heroicon-o-check class="w-4 h-4"></x-heroicon-o-check>
                            </button>
                        </div>
                    </li>
                @empty
                    <li>
                        {{ trans('schedules.no_child_absent') }}
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="px-3 py-2 w-full sm:w-1/2 lg:w-full">
            <h2 class="mb-5 uppercase text-calendar-dark font-bold border-b border-calendar-dark">{{ trans('dashboard.present_title') }}</h2>

            <ul class="space-y-4">
                @forelse($presentUsers as $item)
                    <li wire:key="{{ $item['uuid'] }}">
                        <div class="flex justify-between">
                            <strong class="font-bold pr-4">
                                <a href="{{ route('users.edit', ['user' => $item['user_uuid'], 'type' => 'contacts']) }}">
                                    {{ $item['user_name'] }}
                                </a>
                            </strong>
{{--                            <strong class="font-bold pr-4">--}}
{{--                                <a--}}
{{--                                    href="#"--}}
{{--                                    wire:click.prevent="$emitTo('components.present-modal', 'present-show-modal', {{json_encode($item)}})"--}}
{{--                                >--}}
{{--                                    {{ $item['user_name'] }}--}}
{{--                                </a>--}}
{{--                            </strong>--}}

                            <button
                                type="button"
                                title="{{ trans('schedules.tooltip_approve') }}"
                                wire:click="showPresentModal('{{$item['uuid']}}','{{$item['user_uuid']}}', '{{$item['user_name']}}')"
                                class="inline-flex items-center justify-between border-transparent rounded-full p-2 text-white bg-indigo-600 focus:outline-none mx-1 cursor-pointer hover:bg-indigo-500"
                            >
                                <x-heroicon-o-x class="w-4 h-4"></x-heroicon-o-x>
                            </button>
                        </div>
                    </li>
                @empty
                    <li>
                        {{ trans('schedules.no_child_present') }}
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

@livewire('components.present-modal')

@livewire('components.present-other-collect-modal')
