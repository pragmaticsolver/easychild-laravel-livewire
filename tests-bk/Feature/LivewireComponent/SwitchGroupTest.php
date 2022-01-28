<?php

namespace Tests\Feature\LivewireComponent;

use App\Models\Group;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SwitchGroupTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create([
            'name' => 'My Org Name',
        ]);
        $this->principal = $this->getUser('Principal', $this->org->id);

        $this->group1 = Group::factory()->create([
            'name' => 'My Group 1',
            'organization_id' => $this->org->id,
        ]);
        $this->group2 = Group::factory()->create([
            'name' => 'My Group 2',
            'organization_id' => $this->org->id,
        ]);

        $this->group1->users()->attach($this->principal);
        $this->group2->users()->attach($this->principal);

        $this->be($this->principal);
    }

    /** @test */
    public function test_a_principal_can_switch_his_group()
    {
        Livewire::test('components.switch-group', [
            'group' => $this->group1->id,
        ])
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                trans('group-class.switch_modal_title'),
                $this->group1->name,
                $this->group2->name,
                trans('group-class.switch_modal_cancel'),
                trans('group-class.switch_modal_submit'),
            ]);
    }

    /** @test */
    public function switch_group_modal_is_visible_when_show_switch_group_modal_event_is_emitted()
    {
        Livewire::test('components.switch-group', [
            'group' => $this->group1->id,
        ])
            ->emit('showSwitchGroupModal')
            ->assertSet('enabled', true);
    }

    /** @test */
    public function group_is_switched_when_selecting_other_group()
    {
        Livewire::test('components.switch-group', [
            'group' => $this->group1->id,
        ])
            ->call('changeGroup', $this->group2->id)
            ->assertSet('enabled', false)
            ->assertSet('group', $this->group2->id)
            ->assertEmitted('principalGroupUpdated');

        $key = 'user-' . $this->principal->id . '-group';
        $this->assertSame($this->group2->id, cache()->get($key));
    }
}
