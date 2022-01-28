<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use App\Models\Schedule;
use App\Notifications\UserEventsNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleApproveTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function manager_can_view_schedule_based_on_date_passed()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $this->get(route('schedules.index', ['date' => now()->addDay()->format('Y-m-d')]))
            ->assertSuccessful()
            ->assertSeeText(now()->addDay()->format('Y-m-d'))
            ->assertSeeText(trans('pagination.not_found', ['type' => trans('schedules.schedule_plural')]));
    }

    /** @test */
    public function manager_can_see_approve_schedules_page()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $this->get(route('schedules.index'))
            ->assertSuccessful()
            ->assertSeeText(trans('schedules.approve_title'));
    }

    /** @test */
    public function manager_can_approve_a_schedule()
    {
        Notification::fake();

        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id, ['mail' => true]);
        $group->users()->attach($user);

        $this->createTestScheduleData($user, now()->addWeek()->startOfWeek()->format('Y-m-d'), '08:00:00', '10:00:00', 'pending');

        $openingTimes = $org->settings['opening_times'];

        $schedule = Schedule::first();

        Livewire::test('schedules.approve-item', compact('schedule', 'openingTimes'))
            ->set('status', 'approved')
            ->call('saveSchedule')
            ->assertSet('status', 'approved')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', ['day' => $schedule->date]),
            ]);

        Notification::assertSentTo(
            $user,
            UserEventsNotification::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $this->assertTrue(in_array('mail', $channels));

                return $notifiable->uuid == $user->uuid;
            }
        );
    }

    /** @test */
    public function manager_can_decline_a_schedule()
    {
        Notification::fake();

        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $group->users()->attach($user);

        $this->createTestScheduleData($user, now()->addWeek()->startOfWeek()->format('Y-m-d'), '08:00:00', '10:00:00', 'pending');

        $openingTimes = $org->settings['opening_times'];

        $schedule = Schedule::first();
        Livewire::test('schedules.approve-item', compact('schedule', 'openingTimes'))
            ->set('status', 'declined')
            ->call('saveSchedule')
            ->assertSet('status', 'declined')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', ['day' => $schedule->date]),
            ]);

        Notification::assertSentTo(
            $user,
            UserEventsNotification::class,
            function ($notification, $channels, $notifiable) use ($user) {
                $this->assertFalse(in_array('mail', $channels));

                return $notifiable->uuid == $user->uuid;
            }
        );
    }

    /** @test */
    public function status_can_be_changed_only_to_approved_or_declined()
    {
        $org = Organization::factory()->create();
        $manager = $this->getUser('Manager', $org->id);
        $this->be($manager);

        $group = Group::factory()->create([
            'organization_id' => $org->id,
        ]);

        $user = $this->getUser('User', $org->id);
        $group->users()->attach($user);

        $this->createTestScheduleData($user, now()->format('Y-m-d'), '08:00:00', '10:00:00', 'approved');

        $openingTimes = $org->settings['opening_times'];

        $schedule = Schedule::first();
        Livewire::test('schedules.approve-item', compact('schedule', 'openingTimes'))
            ->set('status', 'pending')
            ->call('saveSchedule')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.invalid_status'),
            ]);
    }
}
