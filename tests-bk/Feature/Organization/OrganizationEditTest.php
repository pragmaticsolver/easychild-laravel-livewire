<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OrganizationEditTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    private $orgName = 'My Organization name';

    /** @test */
    public function user_with_role_user_cannot_go_to_edit_organization()
    {
        $org = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('User', $org->id);

        $this->be($user);

        $this->get(route('organizations.edit', $org->uuid))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_only'));
    }

    /** @test */
    public function user_with_principal_user_cannot_go_to_edit_organization()
    {
        $org = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('organizations.edit', $org->uuid))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_only'));
    }

    /** @test */
    public function organization_livewire_component_cannot_be_faked_by_user()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('User', $organization->id);

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->assertForbidden();
    }

    /** @test */
    public function organization_livewire_component_cannot_be_faked_by_principal()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Principal', $organization->id);

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->assertForbidden();
    }

    /** @test */
    public function user_with_admin_user_can_go_to_edit_organization()
    {
        $org = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin', $org->id);

        $this->be($user);

        $this->get(route('organizations.edit', $org->uuid))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('organizations.edit')
            ->assertSeeTextInOrder([
                $org->name,
                trans('organizations.name'),
                trans('organizations.house_no'),
                trans('organizations.street'),
                trans('organizations.zip_code'),
                trans('organizations.update'),
            ]);
    }

    /** @test */
    public function name_is_required_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('name', '')
            ->call('updateOrg')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function name_needs_to_be_min_eight_char_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('name', 'aaaaaaa')
            ->call('updateOrg')
            ->assertHasErrors(['name' => 'min']);
    }

    /** @test */
    public function house_no_is_required_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('house_no', '')
            ->call('updateOrg')
            ->assertHasErrors(['house_no' => 'required']);
    }

    /** @test */
    public function street_is_required_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('street', '')
            ->call('updateOrg')
            ->assertHasErrors(['street' => 'required']);
    }

    /** @test */
    public function zip_code_is_required_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('zip_code', '')
            ->call('updateOrg')
            ->assertHasErrors(['zip_code' => 'required']);
    }

    /** @test */
    public function city_is_required_when_editing_organization()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('city', '')
            ->call('updateOrg')
            ->assertHasErrors(['city' => 'required']);
    }

    /** @test */
    public function organization_name_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('name', 'Organization Name')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Organization Name',
        ]);
    }

    /** @test */
    public function organization_house_no_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('house_no', 'House No')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'house_no' => 'House No',
        ]);
    }

    /** @test */
    public function organization_street_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('street', 'Street Addr.')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'street' => 'Street Addr.',
        ]);
    }

    /** @test */
    public function organization_zip_code_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('zip_code', '12345')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'zip_code' => '12345',
        ]);
    }

    /** @test */
    public function organization_city_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.edit', compact('organization'))
            ->set('city', 'City Name')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'city' => 'City Name',
        ]);
    }

    /** @test */
    public function organization_eats_onsite_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        $settings = $organization->settings;

        Livewire::test('organizations.edit', compact('organization'))
            ->set('eatsOnsite', false)
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $settings['eats_onsite'] = false;

        $updatedOrganization = Organization::find($organization->id);
        $this->assertSame($settings, $updatedOrganization->settings);
    }

    /** @test */
    public function organization_schedule_settings_is_updated_in_database()
    {
        $organization = Organization::factory()->create([
            'name' => $this->orgName,
        ]);
        $user = $this->getUser('Admin');

        $this->be($user);

        $settings = $organization->settings;

        Livewire::test('organizations.edit', compact('organization'))
            ->set('availability', 'available')
            ->call('updateOrg')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $settings['availability'] = 'available';

        $updatedOrganization = Organization::find($organization->id);
        $this->assertSame($settings, $updatedOrganization->settings);
    }
}
