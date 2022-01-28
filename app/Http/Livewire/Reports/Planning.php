<?php

namespace App\Http\Livewire\Reports;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Planning extends Component
{
    public $dateRange;

    protected $listeners = [
        'downloadExcel',
        'principalGroupUpdated' => '$refresh',
    ];

    private function getMainData($startDate, $endDate)
    {
        $user = auth()->user();

        $query = User::query();

        if ($user->isPrincipal()) {
            $principalGroup = $user->principal_current_group;

            $query->whereIn('users.id', function ($query) use ($principalGroup) {
                $query->select('u1.id')
                    ->from('users as u1')
                    ->join('group_user', 'group_user.user_id', 'u1.id')
                    ->where('group_user.group_id', $principalGroup->id);
            });
        }

        $users = $query->where('users.role', 'User')
            ->where('users.organization_id', $user->organization_id)
            ->addSelect([
                'group_name' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'users.id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.name')
                    ->limit(1),
                'group_uuid' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'users.id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.uuid')
                    ->limit(1),
            ])
            ->get();

        $users->load(['schedules' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC');
        }]);

        $users->load('contract');

        return $users;
    }

    private function getSharedData()
    {
        $startDate = now()->subWeek()->endOfDay();
        $endDate = now()->subDay()->endOfDay();

        if ($this->dateRange && Str::contains($this->dateRange, '~')) {
            $range = Str::of($this->dateRange)->explode(' ~ ');
            $startDate = Carbon::parse($range[0])->endOfDay();
            $endDate = Carbon::parse($range[1])->endOfDay();
        }

        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');

        $users = $this->getMainData($startDate, $endDate);

        $profileCare = false;
        $orgSettings = auth()->user()->organization->settings;
        if (Arr::has($orgSettings, 'care_option')) {
            $profileCare = $orgSettings['care_option'];
        }

        return [$profileCare, $users, $startDate, $endDate];
    }

    public function render()
    {
        [$profileCare, $users, $startDate, $endDate] = $this->getSharedData();

        return view(
            'livewire.reports.planning',
            compact(
                'profileCare',
                'users',
                'startDate',
                'endDate'
            )
        );
    }

    public function downloadExcel()
    {
        $this->skipRender();

        [$profileCare, $users, $startDate, $endDate] = $this->getSharedData();

        $scheduleData = collect();
        foreach ($users as $user) {
            foreach ($user->schedules as $schedule) {
                $scheduleData->add([
                    'user_id' => $user->id,
                    'user_name' => $user->full_name,
                    'group_name' => $user->group_name,
                    'date' => $schedule->date,
                    'start' => $this->removeSecondsFromTime($schedule->start),
                    'end' => $this->removeSecondsFromTime($schedule->end),
                    'presence_start' => $this->removeSecondsFromTime($schedule->presence_start),
                    'presence_end' => $this->removeSecondsFromTime($schedule->presence_end),
                    'available' => $this->getYesOrNoText($schedule->available),
                    'status' => trans('excel.schedules.status_'.$schedule->status),
                    'breakfast' => $this->getYesOrNoText($schedule->eats_onsite['breakfast']),
                    'lunch' => $this->getYesOrNoText($schedule->eats_onsite['lunch']),
                    'dinner' => $this->getYesOrNoText($schedule->eats_onsite['dinner']),
                    'allergy' => $schedule->allergy ?? '-',
                ]);
            }
        }

        $scheduleData = $scheduleData->sortBy('date');

        if (! $scheduleData->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/".auth()->user()->uuid.'.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid.'.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($scheduleData as $schedule) {
            $writer->addRow([
                trans('excel.group-class.user_id') => $schedule['user_id'],
                trans('excel.group-class.user_name') => $schedule['user_name'],
                trans('excel.group-class.group_name') => $schedule['group_name'],
                trans('excel.schedules.date') => Carbon::parse($schedule['date'])->format(config('setting.format.date')),
                trans('excel.schedules.start') => $schedule['start'],
                trans('excel.schedules.end') => $schedule['end'],
                trans('excel.schedules.presence_start') => $schedule['presence_start'],
                trans('excel.schedules.presence_end') => $schedule['presence_end'],
                trans('excel.schedules.available') => $schedule['available'],
                trans('excel.schedules.status') => $schedule['status'],
                trans('excel.schedules.breakfast') => $schedule['breakfast'],
                trans('excel.schedules.lunch') => $schedule['lunch'],
                trans('excel.schedules.dinner') => $schedule['dinner'],
                trans('excel.schedules.allergy') => $schedule['allergy'],
            ]);
        }

        $fileName = 'schedules-planning'.now()->format(config('setting.format.date')).'.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    private function getYesOrNoText($value)
    {
        return $value ? trans('excel.yes') : trans('excel.no');
    }

    private function removeSecondsFromTime($value)
    {
        if ($value) {
            $value = now()->setTimeFromTimeString($value)->format('H:i');
        }

        return $value ?? '-';
    }

    private function reportGenerate($startDate, $endDate, $users)
    {
        $datePeriod = CarbonPeriod::create($startDate, '1 day', $endDate);

        $mainData = [];
        $usersList = [];
        $userTotalSchedule = [];
        $dateTotalSchedule = [];

        foreach ($users as $usrIdx => $user) {
            $usersList[] = [
                'id' => $user->id,
                'uuid' => $user->uuid,
                'full_name' => $user->full_name,
                'group_name' => $user->group_name,
                'group_uuid' => $user->group_uuid,
                'contract' => $user->contract,
                'edit_route' => route('users.edit', $user->uuid),
                'avatar' => $user->avatar_url,
            ];

            if (! Arr::has($mainData, $user->uuid)) {
                $mainData[$user->uuid] = [];
            }

            foreach ($datePeriod as $date) {
                $currentData = $user->schedules->where('date', $date->format('Y-m-d'))->first();

                if (! Arr::has($userTotalSchedule, $user->uuid)) {
                    $userTotalSchedule[$user->uuid] = [
                        'available' => 0,
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 0,
                    ];
                }

                if (! Arr::has($dateTotalSchedule, $date->format('Y-m-d'))) {
                    $dateTotalSchedule[$date->format('Y-m-d')] = [
                        'available' => 0,
                        'breakfast' => 0,
                        'lunch' => 0,
                        'dinner' => 0,
                    ];
                }

                if ($date->isWeekday()) {
                    if ($currentData && $currentData->available) {
                        $hasBreakfastSelected = $currentData->eats_onsite['breakfast'];
                        $hasLunchSelected = $currentData->eats_onsite['lunch'];
                        $hasDinnerSelected = $currentData->eats_onsite['dinner'];

                        $userTotalSchedule[$user->uuid]['available']++;
                        $hasBreakfastSelected && $userTotalSchedule[$user->uuid]['breakfast']++;
                        $hasLunchSelected && $userTotalSchedule[$user->uuid]['lunch']++;
                        $hasDinnerSelected && $userTotalSchedule[$user->uuid]['dinner']++;

                        $dateTotalSchedule[$date->format('Y-m-d')]['available']++;
                        $hasBreakfastSelected && $dateTotalSchedule[$date->format('Y-m-d')]['breakfast']++;
                        $hasLunchSelected && $dateTotalSchedule[$date->format('Y-m-d')]['lunch']++;
                        $hasDinnerSelected && $dateTotalSchedule[$date->format('Y-m-d')]['dinner']++;

                        $mainData[$user->uuid][] = [
                            'date' => $date->format('Y-m-d'),
                            'date_title' => $date->format('d.'),
                            'type' => 'weekday',
                            'breakfast' => $hasBreakfastSelected,
                            'lunch' => $hasLunchSelected,
                            'dinner' => $hasDinnerSelected,
                            'available' => true,
                        ];
                    } else {
                        $mainData[$user->uuid][] = [
                            'date' => $date->format('Y-m-d'),
                            'date_title' => $date->format('d.'),
                            'type' => 'weekday',
                            'breakfast' => false,
                            'lunch' => false,
                            'dinner' => false,
                            'available' => false,
                        ];
                    }
                } else {
                    if ($date->isSunday() && ! $date->isSameDay($endDate)) {
                        $mainData[$user->uuid][] = [
                            'type' => 'weekend',
                            'date' => $date->format('Y-m-d'),
                        ];
                    }
                }
            }
        }

        return [
            $mainData,
            $usersList,
            $userTotalSchedule,
            $dateTotalSchedule,
        ];
    }
}
