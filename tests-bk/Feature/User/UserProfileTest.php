<?php

namespace Tests\Feature\User;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserProfileTest extends TestCase
{
    use TestHelpersTraits, RefreshDatabase;

    /** @test */
    public function user_can_update_profile_avatar()
    {
        Storage::fake('avatars');

        $img = 'data:image/png;base64,' . base64_encode(UploadedFile::fake()->image('avatar.png'));

        $org = Organization::factory()->create([
            'name' => 'My Org name',
        ]);
        $user = $this->getUser('User', $org->id);
        $this->be($user);

        Livewire::test('users.profile')
            ->set('newAvatar', $img)
            ->call('update')
            ->assertDispatchedBrowserEvent('user-image-update')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('users.update_success'),
            ]);

        $user = User::find($user->id);
        $this->assertNotNull($user->avatar);
        UploadedFile::fake($user->avatar);

        Storage::disk('avatars')->assertExists($user->avatar);
    }
}
