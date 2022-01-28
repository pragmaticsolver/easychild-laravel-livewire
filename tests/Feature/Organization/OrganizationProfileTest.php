<?php

namespace Tests\Feature\Organization;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OrganizationProfileTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function manager_can_update_organization_schedule_setting_on_profile()
    {
        $org = Organization::factory()->create([
            'name' => 'My Org name',
        ]);
        $user = $this->getUser('Manager', $org->id);
        $this->be($user);

        $settings = $org->settings;

        Livewire::test('organizations.profile')
            ->set('eatsOnsite.dinner', false)
            ->set('availability', 'not-available')
            ->call('update')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $updatedOrganization = Organization::find($org->id);
        $this->assertSame('not-available', $updatedOrganization->settings['availability']);
        $this->assertSame(false, $updatedOrganization->settings['eats_onsite']['dinner']);
    }

    /** @test */
    public function manager_can_update_organization_avatar()
    {
        Storage::fake('avatars');

        $img = 'data:image/png;base64,' . base64_encode(UploadedFile::fake()->image('avatar.png'));

        $org = Organization::factory()->create([
            'name' => 'My Org name',
        ]);
        $user = $this->getUser('Manager', $org->id);
        $this->be($user);

        Livewire::test('organizations.profile')
            ->set('newAvatar', $img)
            ->call('update')
            ->assertHasNoErrors()
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('organizations.update_success'),
            ]);

        $org = Organization::first();
        $this->assertNotNull($org->avatar);
        UploadedFile::fake($org->avatar);

        Storage::disk('avatars')->assertExists($org->avatar);
    }
}
