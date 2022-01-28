<?php

namespace Tests\Feature\Parents;

use App\Models\ParentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ParentMiddlewareTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');
        $this->manager = $this->getUser('Manager', $this->org);
        $this->parent = $this->getUser('Parent');

        $this->parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
        ]);
        $this->parent->childrens()->attach($this->child);
    }

    /** @test */
    public function parent_cannot_use_login_token_to_goto_dashboard_without_completing_signup()
    {
        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $loginUrl = URL::signedRoute('parent.signup', [
            'token' => $this->parent->token,
        ]);

        $this->get($loginUrl)
            ->assertSeeLivewire('auth.parent-signup');

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function parent_cannot_use_signup_token_to_goto_dashboard_without_completing_signup()
    {
        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $loginUrl = URL::signedRoute('parent.signup', [
            'token' => $this->parentLink->token,
        ]);

        $this->get($loginUrl)
            ->assertSeeLivewire('auth.parent-signup');

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function parent_signing_in_without_completing_signup_will_be_taken_to_signup_page()
    {
        TestTime::freeze(now()->startOfMinute());

        $this->parent->update([
            'given_names' => null,
            'last_name' => null,
        ]);

        $this->actingAs($this->parent);

        $redirectUrl = URL::signedRoute('parent.signup', [
            'token' => $this->parent->token,
        ]);

        $this->get(route('dashboard'))
            ->assertRedirect($redirectUrl);

        // $this->assertFalse($this->isAuthenticated());
    }
}
