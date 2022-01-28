<?php

namespace App\Http\Livewire\Organizations;

use App\Http\Livewire\Component;
use App\Models\Organization;
use App\Traits\HasAvatarFileUploader;

class Create extends Component
{
    use HasAvatarFileUploader;

    public $name = '';
    public $street = '';
    public $house_no = '';
    public $zip_code = '';
    public $city = '';
    public $phone = '';

    public $avatar;
    public $newAvatar;

    public $serviceMessages;
    public $serviceInformations;

    public $eatsOnsite = [
        'breakfast' => true,
        'lunch' => true,
        'dinner' => true,
    ];
    public $availability;

    protected $listeners = ['addOrg'];

    public function mount()
    {
        $this->authorize('create', Organization::class);

        $this->availability = 'not-available';
        $this->serviceMessages = false;
        $this->serviceInformations = false;
    }

    public function addOrg()
    {
        $this->authorize('create', Organization::class);

        $availabilityOptions = collect(config('setting.userAvailabilityOptions'))->pluck('value')->all();
        $options = join(',', $availabilityOptions);

        $this->validate([
            'name' => ['required', 'min:8'],
            'street' => ['required'],
            'house_no' => ['required'],
            'zip_code' => ['required'],
            'city' => ['required'],
            'eatsOnsite.*' => ['required', 'boolean'],
            'availability' => ['required', 'in:' . $options],
            'serviceMessages' => ['required', 'boolean'],
            'serviceInformations' => ['required', 'boolean'],
        ]);

        $data = [
            'name' => $this->name,
            'street' => $this->street,
            'house_no' => $this->house_no,
            'zip_code' => $this->zip_code,
            'city' => $this->city,
        ];

        $organization = Organization::create($data);

        $settings = $organization->settings;
        $settings['eats_onsite'] = $this->eatsOnsite;
        $settings['availability'] = $this->availability;
        $settings['phone'] = $this->phone;

        $settings['access'] = [
            'messages' => $this->serviceMessages,
            'informations' => $this->serviceInformations,
        ];

        $avatar = $this->uploadImage($this->newAvatar, $organization, 'avatar');

        $updateData = [
            'settings' => $settings,
            'avatar' => $avatar,
        ];
        $organization->update($updateData);

        session()->flash('success', trans('organizations.create_success', ['name' => $this->name]));

        return redirect(route('organizations.edit', $organization->uuid));
    }

    public function render()
    {
        $availabilityOptions = config('setting.userAvailabilityOptions');

        return view('livewire.organizations.create', compact('availabilityOptions'));
    }
}
