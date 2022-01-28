<?php

namespace App\Http\Livewire\Users\Profile;

use App\Http\Livewire\Component;
use App\Models\User;
use App\Traits\HasAvatarFileUploader;

class ChildSetting extends Component
{
    use HasAvatarFileUploader;

    public User $child;

    public $newAvatar;
    public $avatar;

    public $eatsOnsite = [];

    public function mount(User $child)
    {
        $this->child = $child;

        $this->initUserData();
    }

    private function initUserData()
    {
        $this->avatar = $this->child->avatar_url;

        $this->eatsOnsite = $this->getValueByKey($this->child->settings, 'eats_onsite', $this->eatsOnsiteOrgDefaults);
    }

    public function rules()
    {
        return [
            'eatsOnsite' => 'array',
            'eatsOnsite.breakfast' => ['nullable', 'boolean'],
            'eatsOnsite.lunch' => ['nullable', 'boolean'],
            'eatsOnsite.dinner' => ['nullable', 'boolean'],
        ];
    }

    public function updatedEatsOnsite($value, $key)
    {
        $this->authorize('updateAvatarAndEatsOnsite', $this->child);

        $settings = $this->child->settings;
        if (! $settings) {
            $settings = [];
        }

        $settings['eats_onsite'][$key] = $value;

        $this->child->update([
            'settings' => $settings,
        ]);
    }

    public function updatedNewAvatar($newAvatar)
    {
        $this->authorize('updateAvatarAndEatsOnsite', $this->child);

        $filename = $this->uploadImage($newAvatar, $this->child, 'avatar');

        $this->child->update([
            'avatar' => $filename,
        ]);
    }

    public function getEatsOnsiteOrgDefaultsProperty()
    {
        $org = $this->child->organization;

        $organizationSettings = optional($org)->settings;
        if (! $organizationSettings) {
            $organizationSettings = [];
        }

        return $this->getValueByKey($organizationSettings, 'eats_onsite', config('setting.organizationScheduleSettings.eats_onsite'));
    }

    public function render()
    {
        return view('livewire.users.profile.child-setting');
    }
}
