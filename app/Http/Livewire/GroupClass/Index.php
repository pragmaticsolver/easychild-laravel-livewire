<?php

namespace App\Http\Livewire\GroupClass;

use App\Events\ScheduleUpdated;
use App\Http\Livewire\Component;
use App\Models\Schedule;
use App\Traits\HasSchedulePresenceEditor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Index extends Component
{
    use HasSchedulePresenceEditor;

    public $showAll = false;
    public $date;
    public $isCurrentDay;
    public $presenceStart;
    public $presenceEnd;

    protected $listeners = [
        'principalGroupUpdated' => '$refresh',
        'userPresenceUpdated' => '$refresh',
        'presence-end' => 'setPresenceEnd',
        'editSchedulePresenceTime',
    ];

    protected $queryString = [
        'date',
    ];

    public function mount()
    {
        $now = $this->getNextOrPrevWeekday();

        if (request()->has('date') && request()->date) {
            $now = $this->getNextOrPrevWeekday(Carbon::parse(request()->date));
        }

        if (!$this->date) {
            $this->date = $now->format('Y-m-d');
        }
    }

    public function setPresenceEnd($params)
    {
        $schedule = Schedule::findByUUIDOrFail($params['schedule_uuid']);

        $date = Carbon::parse($schedule->date);

        $this->isCurrentDay = $date->isToday();

        $this->presenceStart = $this->removeSecondsFromTime($schedule->presence_start);
        $this->presenceEnd = $this->removeSecondsFromTime($schedule->presence_end);

        if (! $this->isCurrentDay) {
            return false;
        }

        $contactName = $params['contact_name'];

        abort_if(! $this->endBtnEnabled(), 401);

        $now = now();

        $this->presenceEnd = $now->format('H:i');


        $this->authorize('update', $schedule);

        $schedule->update([
            'presence_end' => $this->presenceEnd,
        ]);

        ScheduleUpdated::dispatch($schedule, [
            'type' => 'leave',
            'trigger_type' => 'user',
            'triggred_id' => auth()->id(),
            'note' => $contactName != '' ? trans('attendances.collected_by', ['name' => $contactName]) : null
        ]);

        $this->emitTo('group-class.item', $schedule->uuid, $schedule);

        $this->emitUp('userPresenceUpdated');
    }

    public function endBtnEnabled()
    {
        if (! $this->isCurrentDay) {
            return false;
        }

        if (is_null($this->presenceStart)) {
            return false;
        }

        if (is_null($this->presenceEnd)) {
            return true;
        }

        return false;
    }

    private function removeSecondsFromTime($time)
    {
        if ($time && Str::length($time) === 8) {
            return (string) Str::of($time)->replaceLast(':00', '');
        }

        return $time;
    }

    public function prevDay()
    {
        $carbonDate = $this->getNextOrPrevWeekday(Carbon::parse($this->date), 'prev');
        $this->date = $carbonDate->format('Y-m-d');
    }

    public function nextDay()
    {
        $carbonDate = $this->getNextOrPrevWeekday(Carbon::parse($this->date), 'next');
        $this->date = $carbonDate->format('Y-m-d');
    }

    public function getIsCurrentDayProperty()
    {
        $date = Carbon::parse($this->date);

        return $date->isToday();
    }

    public function showAllItems()
    {
        $this->showAll = true;
    }

    public function download()
    {
        [$schedules, $group] = Schedule::todaysSchedule($this->showAll, $this->date);

        if (!$schedules->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/" . auth()->user()->uuid . '.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid . '.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($schedules as $schedule) {
            $writer->addRow([
                trans('excel.group-class.user_id') => $schedule['user']->id,
                trans('excel.group-class.user_name') => $schedule['user']->full_name,
                trans('excel.group-class.group_name') => $schedule['group_name'],

                trans('excel.schedules.date') => Carbon::parse($schedule['date'])->format(config('setting.format.date')),
                trans('excel.schedules.start') => $schedule['start'] ?? '-',
                trans('excel.schedules.end') => $schedule['end'] ?? '-',
                trans('excel.schedules.presence_start') => $schedule['presence_start'] ?? '-',
                trans('excel.schedules.presence_end') => $schedule['presence_end'] ?? '-',
                trans('excel.schedules.available') => $this->getYesOrNoText($schedule['available']),
                trans('excel.schedules.status') => trans('excel.schedules.status_' . $schedule['status']),
                trans('excel.schedules.breakfast') => $this->getYesOrNoText($schedule['eats_onsite']['breakfast']),
                trans('excel.schedules.lunch') => $this->getYesOrNoText($schedule['eats_onsite']['lunch']),
                trans('excel.schedules.dinner') => $this->getYesOrNoText($schedule['eats_onsite']['dinner']),
                trans('excel.schedules.allergy') => $schedule['allergy'],
            ]);
        }

        $fileName = 'presence-schedules-' . now()->format(config('setting.format.date')) . '.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    private function getYesOrNoText($value)
    {
        return $value ? trans('excel.yes') : trans('excel.no');
    }

    public function render()
    {
        $formattedDate = Carbon::parse($this->date);

        if (!$formattedDate->isToday()) {
            $this->showAll = false;
        }

        $formattedDate = $formattedDate->format(config('setting.format.date'));

        [$schedules, $group] = Schedule::todaysSchedule($this->showAll, $this->date);

        $presentUsers = 0;
        $totalUsers = count($schedules);

        foreach ($schedules as $schedule) {
            if ($schedule['presence_start'] && !$schedule['presence_end']) {
                $presentUsers++;
            }
        }

        return view('livewire.group-class.index', compact('schedules', 'group', 'totalUsers', 'presentUsers', 'formattedDate'));
    }
}
