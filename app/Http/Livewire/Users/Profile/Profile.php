<?php

namespace App\Http\Livewire\Users\Profile;

use App\Http\Livewire\Component;
use App\Models\User;
use App\Rules\PasswordPolicyRule;
use App\Traits\HasAvatarFileUploader;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class Profile extends Component
{
    use HasAvatarFileUploader;

    public $given_names;
    public $last_name;
    public $email;
    public $phone;
    public $avatar;
    public $password;
    public $password_confirmation;

    public $newAvatar;
    public $mail;
    public $sms;
    public $pushNotification;

    public $lang;

    public $userPassword;
    public $logoutOtherDeviceModalActive = false;

    public function mount()
    {
        $this->initializeUserData();
    }

    public function getAllAvailableLanguagesProperty()
    {
        return [
            'ar',
            'de',
            'en',
            'es',
            'fr',
            'tr',
            'hi',
        ];
    }

    public function switchLanguage($newLang)
    {
        if ($newLang != $this->lang) {
            $settings = auth()->user()->settings;

            if (! $settings) {
                $settings = [];
            }

            $settings['lang'] = $newLang;
            auth()->user()->update([
                'settings' => $settings,
            ]);

            $this->lang = $newLang;

            return redirect()->route('users.profile');
        }
    }

    private function initializeUserData()
    {
        $user = auth()->user();

        $this->given_names = $user->given_names;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->password = '';
        $this->password_confirmation = '';

        $this->avatar = $user->avatar_url;

        $settings = $user->settings;
        if (! $settings) {
            $settings = [];
        }

        $this->phone = $this->getValueByKey($settings, 'phone', '');
        $this->sms = $this->getValueByKey($settings, 'sms', false);

        $this->mail = $this->getValueByKey($settings, 'mail', false);
        $this->pushNotification = $this->getValueByKey($settings, 'push', false);

        $this->lang = $this->getValueByKey($settings, 'lang', config('app.locale'));

        $this->dispatchBrowserEvent('user-image-update', $this->avatar ?: '');
    }

    public function updatedPushNotification($value)
    {
        $user = auth()->user();
        $settings = $user->settings;
        $settings['push'] = $value;
        $user->update([
            'settings' => $settings,
        ]);

        $this->dispatchBrowserEvent('user-push-notification-toggle', $value);
    }

    public function updatedSms($value)
    {
        $user = auth()->user();
        $settings = $user->settings;
        $settings['sms'] = $value;
        $user->update([
            'settings' => $settings,
        ]);
    }

    public function updatedMail($value)
    {
        if (auth()->user()->isPrincipal()) {
            return;
        }

        $user = auth()->user();
        $settings = $user->settings;
        $settings['mail'] = $value;
        $user->update([
            'settings' => $settings,
        ]);
    }

    public function update()
    {
        $this->validate([
            'given_names' => 'required',
            'last_name' => 'required',
            'email' => [
                'nullable',
                Rule::requiredIf(function () {
                    return auth()->user()->role != 'Principal';
                }),
                'email',
                Rule::unique(User::class)->ignore(auth()->user()->uuid, 'uuid'),
            ],
            'phone' => [
                'nullable',
                'starts_with:15,16,17',
                'digits:11',
            ],
            'password' => [
                'nullable',
                'confirmed',
                new PasswordPolicyRule,
            ],
        ]);

        $user = auth()->user();
        $settings = $user->settings;
        if (! $settings) {
            $settings = [];
        }

        if ($this->phone) {
            $settings['phone'] = $this->phone;
        }

        $data = [
            'given_names' => $this->given_names,
            'last_name' => $this->last_name,
            'password' => $this->password,
            'settings' => $settings,
        ];

        if (! auth()->user()->isPrincipal()) {
            $data['email'] = $this->email;
        }

        $filename = $this->uploadImage($this->newAvatar, $user, 'avatar');

        $data['avatar'] = $filename;

        auth()->user()->update($data);

        $this->initializeUserData();
        $this->emitMessage('success', trans('users.update_success'));
    }

    public function showLogoutOtherDeviceModal()
    {
        $this->dispatchBrowserEvent('confirming-logout-other-browser-sessions');
        $this->userPassword = '';
        $this->logoutOtherDeviceModalActive = true;
    }

    public function logoutOtherBrowserSessions()
    {
        $this->resetErrorBag();

        if (! Hash::check($this->userPassword, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'userPassword' => [trans('extras.logout-other.password_error')],
            ]);
        }

        session()->regenerate();
        auth()->logoutOtherDevices($this->userPassword);

        $this->logoutOtherDeviceModalActive = false;

        $this->emitMessage('success', trans('extras.logout-other.successful'));
    }

    public function render()
    {
        $iCalRoute = URL::signedRoute('ical', [
            'token' => encrypt(auth()->user()->uuid),
        ], now()->addYears(2));

        return view('livewire.users.profile.profile', compact('iCalRoute'));
    }
}
