<?php

namespace Tests\Feature\Parents;

use App\Models\ParentLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class ChildSwitchLogicTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org1, $this->group1, $this->child1] = $this->getOrgGroupUser('User');
        [$this->org2, $this->group2, $this->child2] = $this->getOrgGroupUser('User');

        $this->manager = $this->getUser('Manager', $this->org1->id);
        $this->principal = $this->getUser('Principal', $this->org1->id);

        $this->parent = $this->getUser('Parent');

        ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child1->id,
            'token' => Str::random(),
            'linked' => true,
        ]);
        ParentLink::create([
            'email' => $this->parent->email,
            'child_id' => $this->child2->id,
            'token' => Str::random(),
            'linked' => true,
        ]);

        $this->parent->childrens()->sync([$this->child1->id, $this->child2->id]);
    }

    /** @test */
    public function cannot_access_child_switch_component_without_being_parent()
    {
        Livewire::test('parent.child-switcher')
            ->assertUnauthorized();
    }

    /** @test */
    public function cannot_access_child_switch_component_by_manager()
    {
        $this->actingAs($this->manager);

        Livewire::test('parent.child-switcher')
            ->assertUnauthorized();
    }

    /** @test */
    public function cannot_access_child_switch_component_by_principal()
    {
        $this->actingAs($this->principal);

        Livewire::test('parent.child-switcher')
            ->assertUnauthorized();
    }

    /** @test */
    public function parent_can_access_the_child_switcher_component()
    {
        $this->actingAs($this->parent);

        Livewire::test('parent.child-switcher')
            ->assertSuccessful()
            ->assertSet('parent', $this->parent);
    }

    /** @test */
    public function parent_can_see_the_current_child()
    {
        $this->actingAs($this->parent);
        $child = $this->parent->parent_current_child;

        Livewire::test('parent.child-switcher')
            ->assertSet('current', $child);
    }

    /** @test */
    public function parent_can_switch_to_other_available_child()
    {
        $this->actingAs($this->parent);

        $this->parent->setParentCurrentChild($this->child1);
        $child1 = $this->parent->parent_current_child;

        Livewire::test('parent.child-switcher')
            ->assertSet('current', $child1)
            ->call('switchChild', $this->child2->uuid)
            ->assertEmitted('parent.child-switched', $this->child2->id);

        $child2 = $this->parent->parent_current_child;

        $this->assertSame($this->child2->uuid, $child2->uuid);
    }

    /** @test */
    public function check_all_childrens_are_loaded()
    {
        $this->actingAs($this->parent);

        $data = Livewire::test('parent.child-switcher')
            ->viewData('children');

        $this->assertCount(2, $data);
        $this->assertTrue($data->contains($this->child1->id));
        $this->assertTrue($data->contains($this->child2->id));
    }
}
