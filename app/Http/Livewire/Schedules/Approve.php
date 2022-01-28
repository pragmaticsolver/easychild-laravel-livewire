<?php

namespace App\Http\Livewire\Schedules;

use App\Http\Livewire\Component;
use App\Models\Group;
use App\Services\OrganizationSchedules;
use App\Traits\HasIndexPageFilters;
use App\Traits\HasSorting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;
use Spatie\SimpleExcel\SimpleExcelWriter;

class Approve extends Component
{
    use WithPagination, HasSorting, HasIndexPageFilters;

    public $date;
    public $groupUuid = null;
    public $search;
    public $placeholder;

    protected $queryString = [
        'date',
        'search' => ['except' => ''],
        'sortBy' => ['except' => ''],
        'sortOrder' => ['except' => 'ASC'],
        'page',
    ];

    protected $sortableCols = [
        'users.given_names',
    ];

    protected $listeners = [
        'principalGroupUpdated',
    ];

    public $colFilters = [
        'user_name' => [
            'filter' => null,
            'query' => null,
        ],
        'available' => [
            'filter' => null,
            'query' => null,
        ],
        'eats_onsite_breakfast' => [
            'filter' => null,
            'query' => null,
        ],
        'eats_onsite_lunch' => [
            'filter' => null,
            'query' => null,
        ],
        'eats_onsite_dinner' => [
            'filter' => null,
            'query' => null,
        ],
    ];

    protected $colFiltersMap = [
        'user_name' => [
            'key' => 'user_name',
            'normal' => true,
        ],
        'available' => [
            'key' => 'available',
            'normal' => false,
        ],
        'eats_onsite_breakfast' => [
            'key' => 'eats_onsite.breakfast',
            'normal' => false,
        ],
        'eats_onsite_lunch' => [
            'key' => 'eats_onsite.lunch',
            'normal' => false,
        ],
        'eats_onsite_dinner' => [
            'key' => 'eats_onsite.dinner',
            'normal' => false,
        ],
    ];

    public function paginationView()
    {
        return 'vendor.livewire.pagination-links';
    }

    public function mount($date = null)
    {
        $this->placeholder = trans('extras.search');

        $this->principalGroupUpdated();

        $now = $this->getNextOrPrevWeekday();

        if ($date) {
            $this->date = $date;
        }

        if (request()->has('date') && request()->date) {
            $now = $this->getNextOrPrevWeekday(Carbon::parse(request()->date));
        }

        if (!$this->date) {
            $this->date = $now->format('Y-m-d');
        }

        $this->page = 1;
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

    public function getFormattedDateProperty()
    {
        return Carbon::parse($this->date)->format(config('setting.format.date'));
    }

    public function principalGroupUpdated()
    {
        $user = auth()->user();

        if ($user->isPrincipal()) {
            $this->groupUuid = $user->principal_current_group ? $user->principal_current_group->uuid : null;
        }
    }

    public function getMainQueryProperty()
    {
        $scheduleClass = new OrganizationSchedules(true, $this->modelFilter);
        $scheduleClass->setDate($this->date);

        $scheduleClass->setOrganization(auth()->user()->organization);

        if (auth()->user()->isPrincipal()) {
            $group = Group::whereUuid($this->groupUuid)->first();

            $scheduleClass->setGroup($group);
        }

        return $scheduleClass->fetch($this->search, $this->sortBy, $this->sortOrder);
    }

    public function download()
    {
        $schedules = $this->mainQuery;

        if (!$schedules->count()) {
            return $this->emitMessage('success', trans('extras.cannot_download_empty_excel'));
        }

        $path = "app/xlsx/" . auth()->user()->uuid . '.xlsx';
        Storage::disk('xlsx')->put(auth()->user()->uuid . '.xlsx', '');

        $writer = SimpleExcelWriter::create(storage_path($path));
        foreach ($schedules as $schedule) {
            $writer->addRow([
                trans('excel.schedules.user_id') => $schedule['user']->id,
                trans('excel.schedules.user_name') => $schedule['user']->full_name,
                trans('excel.schedules.group_name') => $schedule['group_name'],
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

        $fileName = 'schedules-' . $this->date . '.xlsx';

        return response()->download(storage_path($path), $fileName)->deleteFileAfterSend(true);
    }

    private function getYesOrNoText($value)
    {
        return $value ? trans('excel.yes') : trans('excel.no');
    }

    public function render()
    {
        $schedules = $this->mainQuery;

        $openingTimes = auth()->user()->organization->settings['opening_times'];

        return view('livewire.schedules.approve', compact('schedules', 'openingTimes'));
    }
}
