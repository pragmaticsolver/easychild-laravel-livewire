<?php

namespace Tests\Feature\Schedule;

use App\Models\Group;
use App\Models\Organization;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ScheduleCreateByUserTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create([
            'name' => 'My Org Name',
        ]);

        $this->group = Group::factory()->create([
            'name' => 'My Group 1',
            'organization_id' => $this->org->id,
        ]);

        $this->user = $this->getUser('User', $this->org->id);
        $this->group->users()->attach($this->user);

        $this->schedule = Schedule::factory()->create([
            'user_id' => $this->user->id,
            'date' => now(),
            'start' => null,
            'end' => null,
            'eats_onsite' => [
                'breakfast' => true,
                'lunch' => true,
                'dinner' => true,
            ],
        ]);

        $this->be($this->user);
    }

    protected function getOrgSettingsByKeyValue($obj, $key, $value)
    {
        if (Str::contains($key, '.')) {
            $keys = Str::of($key)->explode('.');
        } else {
            $obj[$key] = $value;
        }

        return $obj;
    }

    /** @test */
    public function can_change_availability_before_last_schedule_update_time()
    {
        $orgSettings = $this->org->settings;
        $orgSettings['schedule_lock_time'] = now()->addMinutes(15)->format('H:i:s');
        $orgSettings['limitations'] = [
            'lead_time' => 0,
            'selection_time' => 5,
        ];

        $this->org->update([
            'settings' => $orgSettings,
        ]);

        Livewire::test('schedules.create-item', [
            'schedule' => $this->schedule,
        ])->assertSet('schedule', $this->schedule)
            ->set('schedule.available', false)
            ->call('saveSchedule', 'available')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', ['day' => $this->schedule->date]),
            ]);
    }

    /** @test */
    public function schedule_will_change_to_pending_if_changed_during_lead_time()
    {
        $orgSettings = $this->org->settings;
        $orgSettings['schedule_lock_time'] = now()->addMinutes(15)->format('H:i:s');
        $orgSettings['limitations'] = [
            'lead_time' => 0,
            'selection_time' => 5,
        ];

        $this->org->update([
            'settings' => $orgSettings,
        ]);

        Livewire::test('schedules.create-item', [
            'schedule' => $this->schedule,
        ])->assertSet('schedule', $this->schedule)
            ->set('schedule.available', false)
            ->call('saveSchedule', 'available')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', ['day' => $this->schedule->date]),
            ])
            ->assertSet('schedule.status', 'pending');
    }

    /** @test */
    public function meal_can_be_changed_upto_meal_lock_time()
    {
        $orgSettings = $this->org->settings;
        $orgSettings['food_lock_time'] = now()->addMinutes(15)->format('H:i:s');
        $orgSettings['limitations'] = [
            'lead_time' => 0,
            'selection_time' => 5,
        ];

        $this->org->update([
            'settings' => $orgSettings,
        ]);

        Livewire::test('schedules.create-item', [
            'schedule' => $this->schedule,
        ])->assertSet('schedule', $this->schedule)
            ->set('schedule.eats_onsite.breakfast', false)
            ->call('saveSchedule', 'meal')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('schedules.update_success', ['day' => $this->schedule->date]),
            ]);
    }

    /** @test */
    public function meal_cannot_be_changed_when_lock_time_is_gone()
    {
        $orgSettings = $this->org->settings;
        $orgSettings['food_lock_time'] = now()->subMinutes(15)->format('H:i:s');
        $orgSettings['limitations'] = [
            'lead_time' => 0,
            'selection_time' => 5,
        ];

        $this->org->update([
            'settings' => $orgSettings,
        ]);

        Livewire::test('schedules.create-item', [
            'schedule' => $this->schedule,
        ])->assertSet('schedule', $this->schedule)
            ->set('schedule.eats_onsite.breakfast', false)
            ->call('saveSchedule', 'meal')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.disabled_schedule'),
            ]);
    }

    /** @test */
    public function cannot_change_availability_after_last_schedule_update_time()
    {
        $orgSettings = $this->org->settings;
        $orgSettings['schedule_lock_time'] = now()->subMinutes(15)->format('H:i:s');
        $orgSettings['limitations'] = [
            'lead_time' => 0,
            'selection_time' => 5,
        ];

        $this->org->update([
            'settings' => $orgSettings,
        ]);

        Livewire::test('schedules.create-item', [
            'schedule' => $this->schedule,
        ])->assertSet('schedule', $this->schedule)
            ->set('schedule.available', false)
            ->call('saveSchedule', 'available')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('schedules.disabled_schedule'),
            ]);
    }
}
