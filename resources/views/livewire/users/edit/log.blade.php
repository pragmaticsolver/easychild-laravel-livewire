<div class="sm:mx-auto sm:w-full sm:max-w-4xl">
    <h1 class="text-lg leading-6 font-semibold text-gray-900 mb-5">
        {{ trans('users.sub-nav.log') }}
    </h1>

    <div class="flex flex-col mb-5">
        <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
            <div class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.date_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-center text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.time_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.category_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.event_title') }}
                            </th>

                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.caused_by_title') }}
                            </th>
                            <th class="px-6 py-3 border-b border-gray-200 bg-gray-100 text-left text-xs leading-4 font-medium text-gray-500">
                                {{ trans('attendances.note_title') }}
                            </th>
                        </tr>
                    </thead>

                    @if ($attendances->count())
                        <tbody class="bg-white">
                            @foreach($attendances as $attendance)
                                <tr wire:key="{{ $attendance->id }}">
                                    <td class="px-6 py-4 text-left border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $attendance->created_at->format(config('setting.format.date')) }}
                                    </td>

                                    <td class="px-6 py-4 text-center border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        {{ $attendance->created_at->format(config('setting.format.time')) }}
                                    </td>

                                    <td class="px-6 py-4 text-left border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        @if ($attendance->typeable_type == App\Models\Schedule::class)
                                            {{ trans('attendances.categories.presence') }}
                                        @else
                                            &nbsp;
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-left border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        @if ($attendance->type == 'enter')
                                            {{ trans('attendances.event_type.enter') }}
                                        @else
                                            {{ trans('attendances.event_type.leave') }}
                                        @endif

                                        @if($attendance->trigger_type == 'user-manual')
                                            <span class="font-bold">({{ trans('attendances.trigger_type.manual') }})</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-left whitespace-nowrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        @if ($attendance->trigger_person_name)
                                            {{ $attendance->trigger_person_name }} ({{ trans('extras.role_' . Str::lower($attendance->trigger_person_role)) }})
                                        @else
                                            @if ($attendance->trigger_type == 'auto')
                                                {{ trans('attendances.trigger_type.auto') }}
                                            @else
                                                {{ trans('attendances.trigger_type.terminal') }}
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-left whitespace-nowrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        @if($attendance->note != null)
{{--                                            {{trans('attendances.collected_by', ['name' => $attendance->note])}}--}}
                                            {{$attendance->note}}
                                        @endif
                                    </td>

                                    {{-- <td class="px-6 py-4 text-right whitespace-nowrap border-b border-gray-200 text-sm leading-5 text-gray-500">
                                        @if ($attendance->trigger_type == 'auto')
                                            <button
                                                type="button"
                                                title="{{ trans('users.log_section.log_edit') }}"
                                                wire:click.prevent="editLog({{ $attendance->id }})"
                                                class="text-indigo-600 hover:text-indigo-900 ml-3 outline-none focus:outline-none"
                                            >
                                                <x-heroicon-o-pencil class="w-5 h-5" />
                                            </button>
                                        @endif
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if(! $attendances->count())
        <x-no-data-found>
            {{ trans('pagination.not_found', ['type' => trans('attendances.title_lower')]) }}
        </x-no-data-found>
    @endif

    <div>
        {{ $attendances->links() }}
    </div>
</div>
