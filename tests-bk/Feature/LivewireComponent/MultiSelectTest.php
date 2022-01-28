<?php

namespace Tests\Feature\LivewireComponent;

use App\Models\Group;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class MultiSelectTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $user = $this->getUser('Admin');
        $this->be($user);

        $this->org = Organization::factory()->create();

        $items = $this->createUsers(10, [
            'organization_id' => $this->org->id,
            'role' => 'User',
        ]);
        $this->items = User::whereIn('uuid', $items->pluck('uuid'))
            ->select(['id', 'uuid', 'given_names'])
            ->get();

        $selected = $this->createUsers(5, [
            'organization_id' => $this->org->id,
            'role' => 'User',
        ]);
        $this->selected = User::whereIn('uuid', $selected->pluck('uuid'))
            ->select(['id', 'uuid', 'given_names'])
            ->get();

        $this->dataProvider = [
            'items' => $this->items,
            'selected' => $this->selected,
            'displayKey' => 'given_names',
            'emitUpWhenUpdated' => [
                'users_id' => 'selected',
            ],
            'listenToEmit' => [
                'items.updated',
                'selected.updated',
            ],
        ];
    }

    /** @test */
    public function multiselect_component_renders_properly()
    {
        Livewire::test('components.multi-select', $this->dataProvider)
            ->assertSuccessful();
    }

    /** @test */
    public function updates_selected_items_array_when_an_item_is_selected()
    {
        $selected = $this->selected->pluck('uuid')->toArray();
        array_push($selected, $this->items->first()->uuid);

        $newSelected = User::whereIn('uuid', $selected)->count();

        $response = Livewire::test('components.multi-select', $this->dataProvider)
            ->call('addSelect', $this->items->first()->uuid);

        $this->assertCount($newSelected, $response->viewData('selected'));
    }

    /** @test */
    public function updates_selected_items_array_when_an_item_is_removed()
    {
        $selected = $this->selected->pluck('uuid')->toArray();
        $removed = array_pop($selected);

        $newSelected = User::whereIn('uuid', $selected)->count();

        $response = Livewire::test('components.multi-select', $this->dataProvider)
            ->call('removeSelect', $removed);

        $this->assertCount($newSelected, $response->viewData('selected'));
    }

    /** @test */
    public function event_is_dispatched_when_item_is_selected_or_removed()
    {
        $selected = $this->selected->pluck('uuid')->toArray();
        array_push($selected, $this->items->first()->uuid);
        $newSelected = User::whereIn('uuid', $selected)
            ->select(['id', 'uuid', 'given_names'])
            ->get();
        ;

        $res = Livewire::test('components.multi-select', $this->dataProvider)
            ->call('removeSelect', $this->items->first()->uuid)
            ->assertEmitted('updateValueByKey');
    }

    /** @test */
    public function new_data_is_set_when_there_is_item_and_selected_data_change_event_emitted()
    {
        $items = Group::factory()->count(10)->create([
            'organization_id' => $this->org->id,
        ]);
        $selected = [];

        $res = Livewire::test('components.multi-select', $this->dataProvider)
            ->emit('items.updated', [
                'key' => 'items',
                'value' => $items,
            ])
            ->emit('items.updated', [
                'key' => 'selected',
                'value' => $selected,
            ])
            ->assertSet('selected', []);

        $this->assertCount($items->count(), $res->viewData('items'));
    }
}
