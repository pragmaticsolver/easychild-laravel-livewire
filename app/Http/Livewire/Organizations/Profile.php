<?php

namespace App\Http\Livewire\Organizations;

use App\Http\Livewire\Component;
use App\Traits\HasAvatarFileUploader;
use Illuminate\Support\Arr;

class Profile extends Component
{
    use HasAvatarFileUploader;

    public $avatar;
    public $newAvatar;
    public $phone;

    public $eatsOnsite;
    public $availability;
    public $foodLockTime;
    public $scheduleLockTime;
    public $autoUpdate;
    public $careOption;
    public $leadTime;
    public $selectionTime;
    public $collectorPopup;

    public function mount()
    {
        $this->initOrganizationDetail();
    }

    private function initOrganizationDetail()
    {
        $org = auth()->user()->organization;
        $this->avatar = $org->avatar_url;

        $settings = $org->settings;

        $this->eatsOnsite = $this->getValueByKey($settings, 'eats_onsite', true);
        $this->autoUpdate = $this->getValueByKey($settings, 'schedule_auto_approve', false);
        $this->careOption = $this->getValueByKey($settings, 'care_option', false);
        $this->availability = $this->getValueByKey($settings, 'availability', 'available');
        $this->foodLockTime = $this->getValueByKey($settings, 'food_lock_time', '08:00:00');
        $this->scheduleLockTime = $this->getValueByKey($settings, 'schedule_lock_time', '08:00:00');
        $this->phone = $this->getValueByKey($settings, 'phone', '');
        $this->collectorPopup = $this->getValueByKey($settings, 'collectorPopup', false);

        $this->leadTime = config('setting.minLeadDays');
        $this->selectionTime = config('setting.minSelectionDays');

        if (Arr::has($settings, 'limitations')) {
            $this->leadTime = $this->getValueByKey($settings['limitations'], 'lead_time', config('setting.minLeadDays'));
            $this->selectionTime = $this->getValueByKey($settings['limitations'], 'selection_time', config('setting.minSelectionDays'));
        }

        $this->newAvatar = null;

        $this->dispatchBrowserEvent('org-image-update', $this->avatar);
    }

    public function update()
    {
        $availabilityOptions = collect(config('setting.userAvailabilityOptions'))->pluck('value')->all();
        $options = join(',', $availabilityOptions);

        $selectTimes = getTimePickerValues('00:00', '23:45', 15, true);
        $selectTimes = join(',', $selectTimes);

        $minLeadTime = config('setting.minLeadDays');
        $minSelectionTime = config('setting.minSelectionDays');

        $this->validate([
            'eatsOnsite.breakfast' => ['required', 'boolean'],
            'eatsOnsite.lunch' => ['required', 'boolean'],
            'eatsOnsite.dinner' => ['required', 'boolean'],
            'autoUpdate' => ['required', 'boolean'],
            'careOption' => ['required', 'boolean'],
            'availability' => ['required', 'in:' . $options],
            'foodLockTime' => ['required', 'in:' . $selectTimes],
            'scheduleLockTime' => ['required', 'in:' . $selectTimes],
            'leadTime' => ['required', 'numeric', 'min:' . $minLeadTime],
            'selectionTime' => ['required', 'numeric', 'min:' . $minSelectionTime],
            'collectorPopup' => ['required', 'boolean']
        ]);

        $org = auth()->user()->organization;
        $filename = $this->uploadImage($this->newAvatar, $org, 'avatar');

        $settings = $org->settings;

        $settings['eats_onsite'] = $this->eatsOnsite;
        $settings['schedule_auto_approve'] = $this->autoUpdate;
        $settings['availability'] = $this->availability;
        $settings['food_lock_time'] = $this->foodLockTime;
        $settings['schedule_lock_time'] = $this->scheduleLockTime;
        $settings['care_option'] = $this->careOption;
        $settings['phone'] = $this->phone;
        $settings['collectorPopup'] = $this->collectorPopup;

        $limitations = [];
        $limitations['lead_time'] = (int) $this->leadTime;
        $limitations['selection_time'] = (int) $this->selectionTime;

        $settings['limitations'] = $limitations;

        $org->update([
            'avatar' => $filename,
            'settings' => $settings,
        ]);

        $this->initOrganizationDetail();
        $this->emitMessage('success', trans('organizations.update_success'));
    }

    public function render()
    {
        $availabilityOptions = config('setting.userAvailabilityOptions');

        return view('livewire.organizations.profile', compact('availabilityOptions'));
    }
}
