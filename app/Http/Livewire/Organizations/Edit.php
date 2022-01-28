<?php

namespace App\Http\Livewire\Organizations;

use App\Http\Livewire\Component;
use App\Models\Organization;
use App\Traits\HasAvatarFileUploader;
use Illuminate\Support\Arr;

class Edit extends Component
{
    use HasAvatarFileUploader;

    public $name = '';
    public $street = '';
    public $house_no = '';
    public $zip_code = '';
    public $city = '';
    public $phone = '';
    public $uuid = null;

    public $avatar;
    public $newAvatar;

    public $eatsOnsite;
    public $availability;
    public $serviceMessages;
    public $serviceInformations;

    public function mount($organization)
    {
        $this->authorize('update', $organization);

        $this->uuid = $organization->uuid;
        $this->name = $organization->name;
        $this->street = $organization->street;
        $this->house_no = $organization->house_no;
        $this->zip_code = $organization->zip_code;
        $this->city = $organization->city;

        $this->avatar = $organization->avatar_url;
        $this->newAvatar = null;

        $settings = $organization->settings;
        if (! $settings) {
            $settings = [];
        }

        $this->eatsOnsite = $this->getValueByKey($settings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
        $this->availability = $this->getValueByKey($settings, 'availability', false);
        $this->phone = $this->getValueByKey($settings, 'phone', '');

        $services = [];

        if (Arr::has($settings, 'access')) {
            $services = $settings['access'];
        }
        $this->serviceMessages = $this->getValueByKey($services, 'messages', false);
        $this->serviceInformations = $this->getValueByKey($services, 'informations', false);
    }

    public function updateOrg()
    {
        $availabilityOptions = collect(config('setting.userAvailabilityOptions'))->pluck('value')->all();
        $options = join(',', $availabilityOptions);

        $this->validate([
            'name' => ['required', 'min:8'],
            'street' => ['required'],
            'house_no' => ['required'],
            'zip_code' => ['required'],
            'city' => ['required'],
            'eatsOnsite.*' => ['required', 'boolean'],
            'serviceMessages' => ['required', 'boolean'],
            'serviceInformations' => ['required', 'boolean'],
            'availability' => ['required', 'in:' . $options],
        ]);

        $org = Organization::findByUUIDOrFail($this->uuid);
        $this->authorize('update', $org);

        $settings = $org->settings;
        $settings['eats_onsite'] = $this->eatsOnsite;
        $settings['availability'] = $this->availability;
        $settings['phone'] = $this->phone;

        $settings['access'] = [
            'messages' => $this->serviceMessages,
            'informations' => $this->serviceInformations,
        ];

        $avatar = $this->uploadImage($this->newAvatar, $org, 'avatar');

        $data = [
            'name' => $this->name,
            'street' => $this->street,
            'house_no' => $this->house_no,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
            'settings' => $settings,
            'avatar' => $avatar,
        ];

        $org->update($data);

        $this->emitMessage('success', trans('organizations.update_success'));
    }

    public function render()
    {
        $availabilityOptions = config('setting.userAvailabilityOptions');

        return view('livewire.organizations.edit', compact('availabilityOptions'));
    }
}
