<?php

namespace App\Http\Livewire\Calendar;

use App\Http\Livewire\Component;
use App\Models\CalendarEvent;
use App\Rules\OrganizationGroupsRule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public CalendarEvent $event;
    public $showModal = false;
    public $files = [];
    public $groups_id = [];

    public $roles = [
        'User' => true,
        'Manager' => false,
        'Principal' => false,
        'Vendor' => false,
    ];

    public $isEditing = false;
    public $editFiles = [];

    protected $listeners = ['updateValueByKey'];

    public function getThemeColorsProperty()
    {
        return collect(config('setting.events.colors'));
    }

    protected function rules()
    {
        $colors = $this->themeColors->join(',');

        $mimes = implode(',', [
            'pdf',
            'jpg', 'jpeg', 'png',
        ]);

        return [
            'event.title' => 'required',
            'event.description' => 'required',
            'event.from' => 'required',
            'event.to' => 'required|after_or_equal:event.from',
            'event.all_day' => 'required|boolean',
            'event.color' => "required|in:{$colors}",
            'files.*' => 'nullable|file|max:10240|mimes:'.$mimes,
            'editFiles.*' => 'array',
            'editFiles.*.name' => 'required',
            'editFiles.*.type' => 'required',
            'editFiles.*.url' => 'required',
            'roles.*' => 'required|boolean',
            'groups_id' => ['array', new OrganizationGroupsRule()],
        ];
    }

    protected $validationAttributes = [
        'event.from' => 'event start date',
        'event.to' => 'event end date',
    ];

    public function mount()
    {
        $this->resetEventItem();
    }

    protected function resetEventItem()
    {
        $this->event = CalendarEvent::make([
            'all_day' => true,
            'color' => $this->themeColors[0],
            'groups' => [],
            'from' => null,
            'to' => null,
        ]);

        $this->files = [];
        $this->editFiles = [];

        $this->dispatchBrowserEvent('date-picker-clear');
    }

    public function createNew()
    {
        $this->resetEventItem();

        $this->groups_id = [];
        $this->reset('roles');

        $this->emit('calendar-events.create.selected-groups.updated', [
            'key' => 'selected',
            'value' => $this->groups_id,
        ]);

        $this->isEditing = false;
        $this->showModal = true;
    }

    public function removeSingleFile($name)
    {
        if (! $this->isEditing || ! $this->event->getKey()) {
            return false;
        }

        $files = [];

        if ($this->editFiles && count($this->editFiles)) {
            foreach ($this->editFiles as $file) {
                if ($file['name'] == $name) {
                    $file['removed'] = true;
                }

                $files[] = $file;
            }
        }

        $this->editFiles = $files;
    }

    public function getCurrentNotRemovedFiles()
    {
        return collect($this->editFiles)->filter(function ($file) {
            return ! (isset($file['removed']) && $file['removed']);
        })->all();
    }

    public function editEvent($eventUuid)
    {
        $event = CalendarEvent::findByUUIDOrFail($eventUuid);
        $this->authorize('update', $event);

        $this->event = $event;

        $this->editFiles = $event->files;
        $this->files = [];

        $availableRoles = ['User', 'Manager', 'Principal', 'Vendor'];
        foreach ($availableRoles as $role) {
            $this->roles[$role] = false;

            if (in_array($role, $this->event->roles)) {
                $this->roles[$role] = true;
            }
        }

        $this->groups_id = $event->groups;

        $this->emit('calendar-events.create.selected-groups.updated', [
            'key' => 'selected',
            'value' => $this->groups_id,
        ]);

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function cancel()
    {
        $this->resetEventItem();

        $this->editFiles = [];
        $this->files = [];

        $this->reset('roles');

        $this->groups_id = [];

        $this->isEditing = false;
        $this->showModal = false;
    }

    protected function safeFileName($name)
    {
        $name = preg_replace("/[^\w\-\.]+/i", '-', $name);

        if (Str::endsWith($name, '-')) {
            $name = Str::of($name)->replaceLast('-', '')->__toString();
        }

        return $name;
    }

    protected function clientFileNames()
    {
        return collect($this->files)->map(function ($item) {
            return $this->safeFileName($item->getClientOriginalName());
        })->all();
    }

    protected function saveFiles()
    {
        $dirName = "events/".$this->event->uuid;

        foreach ($this->files as $file) {
            $file->storeAs($dirName, $this->safeFileName($file->getClientOriginalName()));
        }
    }

    private function updateFiles()
    {
        $this->saveFiles();

        $newFiles = $this->clientFileNames();

        foreach ($this->editFiles as $file) {
            if (isset($file['removed']) && $file['removed']) {
                $this->deleteUploadedFile($file);
            } else {
                $newFiles[] = $file['name'];
            }
        }

        $this->event->files = $newFiles;
    }

    private function deleteUploadedFile($file)
    {
        $fileName = $this->event->uuid.'/'.$file['name'];

        if (Storage::disk('events')->exists($fileName)) {
            Storage::disk('events')->delete($fileName);
        }
    }

    public function submit()
    {
        $this->validate();

        $msg = trans('calendar-events.create_success_msg');

        if ($this->event->getKey()) {
            $msg = trans('calendar-events.update_success_msg');

            $this->updateFiles();
        } else {
            $this->event->creator_id = auth()->id();
            $this->event->files = $this->clientFileNames();
            $this->event->organization_id = auth()->user()->organization_id;
        }

        $groupList = [];
        if (count($this->groups_id)) {
            foreach ($this->groups_id as $g) {
                $groupList[] = (int) $g;
            }
        }

        $roles = [];
        $availableRoles = ['User', 'Manager', 'Principal', 'Vendor'];
        foreach ($availableRoles as $role) {
            if ($this->roles[$role]) {
                $roles[] = $role;
            }
        }

        $this->event->groups = $groupList;
        $this->event->roles = $roles;

        // if ($this->event->all_day) {
        //     $this->event->to = Carbon::parse($this->event->to)->endOfDay();
        // }

        $this->event->save();

        if (! $this->isEditing) {
            $this->saveFiles();
        }

        $this->isEditing = false;
        $this->showModal = false;

        $this->resetEventItem();

        $this->emitMessage('success', $msg);

        $this->emitTo('calendar.index', 'refreshContent');
    }

    private function edit($uuid)
    {
        $event = CalendarEvent::findByUUIDOrFail($uuid);
        $this->authorize('update', $event);

        $this->event = $event;
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.calendar.create');
    }
}
