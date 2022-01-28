<?php

namespace Tests\Feature\LivewireComponent;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class SearchSelectTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function search_select_component_renders_correctly()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $this->createUsers(10, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('components.search-select', [
            'provider' => [
                'model' => 'user',
                'key' => 'id',
                'text' => 'full_name',
            ],
        ])
            ->assertSeeText(trans('extras.no_item_selected'));
    }

    /** @test */
    public function search_select_component_renders_correctly_when_item_already_selected()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $users = $this->createUsers(10, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('components.search-select', [
            'selected' => $users->first()->id,
            'provider' => [
                'model' => 'user',
                'key' => 'id',
                'text' => 'full_name',
            ],
        ])
            ->assertSet('selectedData', $users->first()->full_name);
    }

    /** @test */
    public function search_select_component_renders_correctly_when_cleared()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $users = $this->createUsers(2, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('components.search-select', [
            'selected' => $users->first()->id,
            'provider' => [
                'model' => 'user',
                'key' => 'id',
                'text' => 'full_name',
            ],
        ])
            ->assertSet('selectedData', $users->first()->full_name)
            ->call('clearItem')
            ->assertSet('selectedData', null)
            ->call('selectItem', $users->last()->id)
            ->assertSet('selectedData', $users->last()->full_name)
            ->set('selected', null)
            ->assertSet('selectedData', null);
    }

    /** @test */
    public function it_emits_event_when_value_changed()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $users = $this->createUsers(10, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        Livewire::test('components.search-select', [
            'selected' => null,
            'provider' => [
                'model' => 'user',
                'key' => 'id',
                'text' => 'full_name',
            ],
            'emitUpWhenUpdated' => 'user_id',
            'emitWhenUpdated' => [
                ['components.search-select', 'user_id.updated'],
                ['components.multi-select', 'user_id.updated'],
            ],
        ])
            ->assertSet('emitUpWhenUpdated', 'user_id')
            ->call('selectItem', $users->last()->id)
            ->assertEmitted('user_id.updated', $users->last()->id)
            ->assertEmitted('updateValueByKey', [
                'key' => 'user_id',
                'value' => $users->last()->id,
            ])
            ->call('clearItem')
            ->assertEmitted('user_id.updated')
            ->assertEmitted('updateValueByKey', [
                'key' => 'user_id',
                'value' => null,
            ]);
    }

    /** @test */
    public function test_if_it_after_emit_select_data_are_limited()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $users = $this->createUsers(10, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        $org2 = Organization::factory()->create();
        $user2 = $this->getUser('User', $org2->id);

        $provider = [
            'model' => 'user',
            'key' => 'id',
            'text' => 'full_name',
            'limitKey' => 'organization_id',
            'limitValue' => null,
            'updateOn' => 'edit.organization_id.updated',
        ];

        $response = Livewire::test('components.search-select', [
            'selected' => null,
            'provider' => $provider,
            'listenToEmit' => 'edit.user_id-.updated',
        ])
            ->assertSet('emitWhenUpdated', false)
            ->emit('edit.organization_id.updated', $org->id)
            ->assertSet('provider', array_merge($provider, ['limitValue' => $org->id]))
            ->call('selectItem', $users->last()->id)
            ->assertSet('selectedData', $users->last()->full_name)
            ->call('selectItem', $user2->id)
            ->assertSet('selectedData', null)
            ->emit('edit.user_id-.updated', $users->last()->id)
            ->assertSet('selectedData', $users->last()->full_name);

        $this->assertCount(10, $response->viewData('data'));
    }

    /** @test */
    public function search_using_the_text_field_gives_valid_data()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $org = Organization::factory()->create();
        $user = $this->createUsers(1, [
            'given_names' => 'lskdjflsjdflkjsdlkfj',
            'organization_id' => $org->id,
            'role' => 'User',
        ])->first();

        $this->createUsers(10, [
            'organization_id' => $org->id,
            'role' => 'User',
        ]);

        $provider = [
            'model' => 'user',
            'key' => 'id',
            'text' => 'full_name',
        ];

        $response = Livewire::test('components.search-select', [
            'selected' => $user->id,
            'provider' => $provider,
        ])->assertSet('selectedData', $user->full_name)
            ->call('changeSearch', $user->given_names);

        $this->assertCount(1, $response->viewData('data'));
    }
}
