<?php

namespace Tests\Feature\Parents;

use App\Actions\User\NewParentCreateAction;
use App\Models\ParentLink;
use App\Models\User;
use App\Notifications\NewChildLinkedToParentNotification;
use App\Notifications\NewParentSignupNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class NewParentAssignTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->child] = $this->getOrgGroupUser('User');
        $this->manager = $this->getUser('Manager', $this->org);
        $this->parent = $this->getUser('Parent');

        $this->be($this->manager);
    }

    /** @test */
    public function cannot_see_any_parent_if_no_parent_linked()
    {
        $data = Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->assertSet('user', $this->child);

        $this->assertCount(0, $data->viewData('parents'));
    }

    /** @test */
    public function can_see_list_of_parents_when_parent_are_linked()
    {
        $this->parent->childrens()->attach($this->child);
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
        ]);

        $data = Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->assertSet('user', $this->child)
            ->assertSee($parentLink->email);

        $this->assertCount(1, $data->viewData('parents'));
    }

    /** @test */
    public function can_add_new_parent_to_a_child()
    {
        Notification::fake();

        TestTime::freeze(now()->startOfMinute());
        $mockedParent = User::factory()->make();

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->set('email', $mockedParent->email)
            ->call('submit')
            ->assertSuccessful()
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('parents.new_create_success_msg'),
            ]);

        $this->assertDatabaseHas('parent_links', [
            'email' => $mockedParent->email,
            'child_id' => $this->child->id,
            'linked' => false,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager->id,
            'related_type' => ParentLink::class,
            'action' => NewParentCreateAction::class,
            'due_at' => now()->addMinutes(5),
        ]);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        $this->assertDatabaseHas('users', [
            'role' => 'Parent',
            'email' => $mockedParent->email,
        ]);

        $realParent = User::query()
            ->where('email', $mockedParent->email)
            ->first();

        $this->assertDatabaseHas('parent_child', [
            'child_id' => $this->child->id,
            'parent_id' => $realParent->id,
        ]);

        $this->assertDatabaseCount('custom_jobs', 0);

        Notification::assertSentTo($realParent, NewParentSignupNotification::class);
    }

    /** @test */
    public function can_assign_existing_parent_to_a_child()
    {
        Notification::fake();

        TestTime::freeze(now()->startOfMinute());

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->set('email', $this->parent->email)
            ->call('submit')
            ->assertSuccessful()
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('parents.parent_linked_success_msg'),
            ]);

        $this->assertDatabaseHas('parent_links', [
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'linked' => false,
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager->id,
            'related_type' => ParentLink::class,
            'action' => NewChildLinkedToParentNotification::class,
            'due_at' => now()->addMinutes(5),
            'user_ids' => "[{$this->parent->id}]",
        ]);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        $this->assertDatabaseHas('parent_child', [
            'child_id' => $this->child->id,
            'parent_id' => $this->parent->id,
        ]);

        $this->assertDatabaseCount('custom_jobs', 0);

        Notification::assertSentTo($this->parent, NewChildLinkedToParentNotification::class);
    }

    /** @test */
    public function cannot_resend_signup_email_to_parent_if_already_linked()
    {
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
            'linked' => true,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->call('resendLinkEmail', $parentLink->id)
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('parents.resend_link_email_error_msg'),
            ]);
    }

    /** @test */
    public function can_resend_signup_email_to_parent_if_already_linked()
    {
        Notification::fake();

        TestTime::freeze(now()->startOfMinute());

        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
            'linked' => false,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->call('resendLinkEmail', $parentLink->id)
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('parents.resend_link_email_success_msg'),
            ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager->id,
            'related_type' => ParentLink::class,
            'related_id' => $parentLink->id,
            'action' => NewChildLinkedToParentNotification::class,
            'due_at' => now()->addMinutes(5),
            'user_ids' => "[{$this->parent->id}]",
        ]);

        TestTime::addMinutes(6);

        // run custom job
        $this->artisan('job:custom');

        $this->assertDatabaseCount('custom_jobs', 0);

        Notification::assertSentTo($this->parent, NewChildLinkedToParentNotification::class);
    }

    /** @test */
    public function manager_can_unlink_existing_parent_from_child()
    {
        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
            'linked' => false,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->call('unlinkParent', $parentLink->id)
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('parents.parent_unlinked_success_msg'),
            ]);

        $this->assertDatabaseCount('parent_child', 0);
        $this->assertDatabaseCount('parent_links', 0);
    }

    /** @test */
    public function principal_cannot_unlink_existing_parent_from_child()
    {
        $this->principal = $this->getUser('Principal', $this->org);

        $this->be($this->principal);

        $parentLink = ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child->id,
            'token' => Str::random(),
            'linked' => false,
        ]);
        $this->parent->childrens()->attach($this->child);

        Livewire::test('users.edit.parents', [
            'user' => $this->child,
        ])
            ->call('unlinkParent', $parentLink->id)
            ->assertForbidden();
    }
}
