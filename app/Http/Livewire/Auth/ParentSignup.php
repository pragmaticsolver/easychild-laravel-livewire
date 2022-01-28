<?php

namespace App\Http\Livewire\Auth;

use App\Http\Livewire\Component;
use App\Models\ParentLink;
use App\Models\User;
use App\Rules\PasswordPolicyRule;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ParentSignup extends Component
{
    public User $user;
    public $password;
    public $password_confirmation;
    public $token;

    public function getMainTitleProperty()
    {
        return trans('users.parent.signup_title');
    }

    public function mount()
    {
        $parentLink = ParentLink::query()
            ->where('token', $this->token)
            ->first();

        $user = null;

        if ($parentLink) {
            $user = User::query()
                ->where('email', $parentLink->email)
                ->first();
        } else {
            $user = User::query()
                ->where('role', 'Parent')
                ->where('token', $this->token)
                ->first();
        }

        abort_if(! $user, Response::HTTP_FORBIDDEN);

        $this->user = $user;
    }

    protected function rules()
    {
        return [
            'user.given_names' => ['required'],
            'user.last_name' => ['required'],
            'password' => [
                'nullable',
                'confirmed',
                new PasswordPolicyRule,
            ],
        ];
    }

    public function submit()
    {
        $this->validate();

        if ($this->password) {
            $this->user->password = $this->password;
            $this->user->setRememberToken(Str::random(60));
        }
        $this->user->save();

        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $parentLink = ParentLink::query()
            ->where('token', $this->token)
            ->where('email', $this->user->email)
            ->first();

        if ($parentLink) {
            $parentLink->update([
                'linked' => true,
            ]);
        }

        return $this->sendSuccessResponse();
    }

    private function sendSuccessResponse()
    {
        auth()->login($this->user);
        session()->flash('success', trans('auth.parent_signed_up'));

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.parent-signup');
    }
}
