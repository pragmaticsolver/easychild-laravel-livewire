<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OrganizationCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function user_cannot_view_organization_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser();

        $this->be($user);

        $this->get(route('organizations.create'))
            ->assertRedirect(route('login'));

        $this->assertFalse($this->isAuthenticated());
    }

    /** @test */
    public function principal_cannot_view_organization_create_page()
    {
        [$org, $group, $user] = $this->getOrgGroupUser('Principal');

        $this->be($user);

        $this->get(route('organizations.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', config('setting.errors.admin_only'));
    }

    /** @test */
    public function organization_livewire_component_cannot_be_faked_by_user()
    {
        $user = $this->getUser('User');

        $this->be($user);

        Livewire::test('organizations.create')
            ->assertForbidden();
    }

    /** @test */
    public function organization_livewire_component_cannot_be_faked_by_principal()
    {
        $user = $this->getUser('Principal');

        $this->be($user);

        Livewire::test('organizations.create')
            ->assertForbidden();
    }

    /** @test */
    public function admin_can_view_organization_create_page()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $this->get(route('organizations.create'))
            ->assertSuccessful()
            ->assertSessionDoesntHaveErrors()
            ->assertSeeLivewire('organizations.create')
            ->assertSeeTextInOrder([
                trans('organizations.add_new_title'),
                trans('organizations.name'),
                trans('organizations.house_no'),
                trans('organizations.street'),
                trans('organizations.zip_code'),
                trans('organizations.city'),
                trans('organizations.add'),
            ]);
    }

    /** @test */
    public function name_is_required_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('name', '')
            ->call('addOrg')
            ->assertHasErrors(['name' => 'required']);
    }

    /** @test */
    public function name_needs_to_be_min_eight_char_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('name', 'aaaaaaa')
            ->call('addOrg')
            ->assertHasErrors(['name' => 'min']);
    }

    /** @test */
    public function house_no_is_required_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('house_no', '')
            ->call('addOrg')
            ->assertHasErrors(['house_no' => 'required']);
    }

    /** @test */
    public function street_is_required_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('street', '')
            ->call('addOrg')
            ->assertHasErrors(['street' => 'required']);
    }

    /** @test */
    public function zip_code_is_required_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('zip_code', '')
            ->call('addOrg')
            ->assertHasErrors(['zip_code' => 'required']);
    }

    /** @test */
    public function city_is_required_when_creating_organization()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        Livewire::test('organizations.create')
            ->set('city', '')
            ->call('addOrg')
            ->assertHasErrors(['city' => 'required']);
    }

    /** @test */
    public function organization_is_created_and_persisted_in_database()
    {
        $user = $this->getUser('Admin');

        $this->be($user);

        $name = 'Organization Name';

        Livewire::test('organizations.create')
            ->set('name', $name)
            ->set('house_no', '1')
            ->set('street', 'Street Name')
            ->set('zip_code', '12345')
            ->set('city', 'City Name')
            ->call('addOrg')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('organizations.create_success', ['name' => $name]));

        $this->assertDatabaseHas('organizations', [
            'name' => 'Organization Name',
        ]);
    }

    /** @test */
    public function organization_can_be_created_with_schedule_settings()
    {
        $user = $this->getUser('Admin');
        $this->be($user);

        $name = 'Org name';

        Livewire::test('organizations.create')
            ->set('name', $name)
            ->set('house_no', '1')
            ->set('street', 'Street Name')
            ->set('zip_code', '12345')
            ->set('city', 'City Name')
            ->set('availability', 'available')
            ->set('eatsOnsite.breakfast', true)
            ->set('eatsOnsite.lunch', true)
            ->set('eatsOnsite.dinner', true)
            ->call('addOrg')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('organizations.create_success', ['name' => $name]));

        $org = Organization::first();

        $settings = [];
        $settings['availability'] = 'available';
        $settings['eats_onsite'] = [
            'breakfast' => true,
            'lunch' => true,
            'dinner' => true,
        ];

        $this->assertSame($settings['eats_onsite'], $org->settings['eats_onsite']);
        $this->assertSame($settings['availability'], $org->settings['availability']);
    }

    /** @test */
    public function organization_can_be_created_with_avatar()
    {
        Storage::fake('avatars');

        $img = 'data:image/png;base64,'.base64_encode(UploadedFile::fake()->image('avatar.png'));

        $user = $this->getUser('Admin');
        $this->be($user);
        $name = 'Organization Name';

        Livewire::test('organizations.create')
            ->set('name', $name)
            ->set('house_no', '1')
            ->set('street', 'Street Name')
            ->set('zip_code', '12345')
            ->set('city', 'City Name')
            ->set('newAvatar', $img)
            ->call('addOrg')
            ->assertHasNoErrors()
            ->assertSessionHas('success', trans('organizations.create_success', ['name' => $name]));

        $org = Organization::first();
        $this->assertNotNull($org->avatar);
        UploadedFile::fake($org->avatar);

        Storage::disk('avatars')->assertExists($org->avatar);
    }
}
