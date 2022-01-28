<?php

namespace Tests\Feature\Parents;

use App\Models\ParentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ParentSignupLogicTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');
        $this->manager = $this->getUser('Manager', $this->org);
        $this->parent = $this->getUser('Parent');
    }

    /** @test */
    public function can_view_signup_page_when_clicked_on_login_link()
    {
        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
        ]);
        $this->parent->childrens()->attach($this->child);

        $signupUrl = URL::signedRoute('parent.signup', [
            'token' => $parentLink->token,
        ]);

        $this->get($signupUrl)
            ->assertSuccessful()
            ->assertSeeLivewire('auth.parent-signup');
    }

    /** @test */
    public function cannot_view_signup_page_when_token_is_invalid()
    {
        $signupUrl = URL::signedRoute('parent.signup', [
            'token' => Str::random(),
        ]);

        $this->get($signupUrl)
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', trans('users.parent.signup_invalid_token'));

        Livewire::test('auth.parent-signup', [
            'token' => Str::random(),
        ])->assertForbidden();
    }

    /** @test */
    public function first_name_last_name_and_password_rules_for_parent_signup()
    {
        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $token = Str::random();
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => $token,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('auth.parent-signup', [
            'token' => $token,
        ])
            ->call('submit')
            ->assertHasErrors(['user.given_names', 'user.last_name'])
            ->set('user.given_names', 'First Name')
            ->call('submit')
            ->assertHasErrors(['user.last_name']);

        Livewire::test('auth.parent-signup', [
            'token' => $token,
        ])
            ->set('password', '123')
            ->set('password_confirmation', '')
            ->call('submit')
            ->assertHasErrors(['password' => 'confirmed']);

        Livewire::test('auth.parent-signup', [
            'token' => $token,
        ])
            ->set('password', '123')
            ->set('password_confirmation', '123')
            ->call('submit')
            ->assertHasErrors(['password']) // error for min
            ->set('password', '1234567891112')
            ->set('password_confirmation', '1234567891112')
            ->call('submit')
            ->assertHasErrors(['password']) // error for symbol
            ->set('password', '$$$$$$$$$$$$A')
            ->set('password_confirmation', '$$$$$$$$$$$$A')
            ->call('submit')
            ->assertHasErrors(['password']) // error for lower character
            ->set('password', '$$$$$$$$$$$$a')
            ->set('password_confirmation', '$$$$$$$$$$$$a')
            ->call('submit')
            ->assertHasErrors(['password']) // error for upper character
            ->set('password', 'abcdefghijklmnopA')
            ->set('password_confirmation', 'abcdefghijklmnopA')
            ->call('submit')
            ->assertHasErrors(['password']); // error for number

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function parent_can_signup_when_all_condition_match()
    {
        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $token = Str::random();
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => $token,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('auth.parent-signup', [
            'token' => $token,
        ])
            ->set('user.given_names', 'First Name')
            ->set('user.last_name', 'Last Name')
            ->set('password', '123456789$aA')
            ->set('password_confirmation', '123456789$aA')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('auth.parent_signed_up'))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('parent_links', [
            'id' => $parentLink->id,
            'linked' => true,
        ]);

        $this->assertAuthenticatedAs($this->parent);
    }

    /** @test */
    public function parent_are_taken_to_dashboard_when_account_is_already_setup()
    {
        $token = Str::random();
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => $token,
        ]);
        $this->parent->childrens()->attach($this->child);

        $signupUrl = URL::signedRoute('parent.signup', [
            'token' => $parentLink->token,
        ]);

        $this->get($signupUrl)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', trans('auth.welcome_signed_up_already'));

        $this->assertDatabaseHas('parent_links', [
            'id' => $parentLink->id,
            'linked' => true,
        ]);

        $this->assertAuthenticatedAs($this->parent);
    }
}
