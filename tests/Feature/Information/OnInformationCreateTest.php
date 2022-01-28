<?php

namespace Tests\Feature\Information;

use App\Models\CustomJob;
use App\Models\Information;
use App\Notifications\InformationAddedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OnInformationCreateTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        TestTime::freeze(now()->startOfWeek());

        [$this->org1, $this->group1, $this->user1] = $this->getOrgGroupUser();
        [$this->org2, $this->group2, $this->user2] = $this->getOrgGroupUser();
        [$this->org3, $this->group3, $this->user3] = $this->getOrgGroupUser();

        $this->manager1 = $this->getUser('Manager', $this->org1->id);
        $this->manager2 = $this->getUser('Manager', $this->org1->id);
        $this->principal1 = $this->getUser('Principal', $this->org1->id);
        $this->group1->users()->attach($this->principal1);

        $this->principal2 = $this->getUser('Principal', $this->org2->id);
        $this->manager3 = $this->getUser('Manager', $this->org2->id);
        $this->group2->users()->attach($this->principal2);

        $this->principal3 = $this->getUser('Principal', $this->org3->id);
        $this->group3->users()->attach($this->principal3);

        $this->parent1 = $this->getUser('Parent');
        $this->parent1->childrens()->attach($this->user1);
        $this->parent1->childrens()->attach($this->user2);

        $this->parent2 = $this->getUser('Parent');

        $this->vendor1 = $this->getUser('Vendor', $this->org1->id);
        $this->vendor2 = $this->getUser('Vendor', $this->org2->id);

        $this->be($this->manager1);

        $this->newInfoTitle = 'New Info title 1';

        $this->fakeFile = UploadedFile::fake()->create('pdf.pdf');
    }

    /** @test */
    public function all_manager_except_logged_in_user_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', false)
            ->set('roles.Principal', false)
            ->set('roles.Vendor', false)
            ->set('roles.Manager', true)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["Manager"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->manager2->id, $userIds);

        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
        $this->assertNotContains($this->vendor1->id, $userIds);
        $this->assertNotContains($this->vendor2->id, $userIds);
    }

    /** @test */
    public function all_principal_user_of_same_organization_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', false)
            ->set('roles.Principal', true)
            ->set('roles.Vendor', false)
            ->set('roles.Manager', false)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["Principal"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->principal1->id, $userIds);

        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
        $this->assertNotContains($this->vendor1->id, $userIds);
        $this->assertNotContains($this->vendor2->id, $userIds);
    }

    /** @test */
    public function all_vendor_user_of_same_organization_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', false)
            ->set('roles.Principal', false)
            ->set('roles.Vendor', true)
            ->set('roles.Manager', false)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["Vendor"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->vendor1->id, $userIds);

        $this->assertNotContains($this->vendor2->id, $userIds);
        $this->assertNotContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent1->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
    }

    /** @test */
    public function all_parent_user_of_same_organization_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', true)
            ->set('roles.Principal', false)
            ->set('roles.Vendor', false)
            ->set('roles.Manager', false)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["User"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->parent1->id, $userIds);

        $this->assertNotContains($this->principal1->id, $userIds);
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
        $this->assertNotContains($this->vendor1->id, $userIds);
        $this->assertNotContains($this->vendor2->id, $userIds);
    }

    /** @test */
    public function all_parent_and_principal_user_of_same_organization_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', true)
            ->set('roles.Principal', true)
            ->set('roles.Vendor', false)
            ->set('roles.Manager', false)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["User","Principal"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->parent1->id, $userIds);
        $this->assertContains($this->principal1->id, $userIds);

        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);
        $this->assertNotContains($this->manager2->id, $userIds);
        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
        $this->assertNotContains($this->vendor1->id, $userIds);
        $this->assertNotContains($this->vendor2->id, $userIds);
    }

    /** @test */
    public function all_user_of_same_organization_get_notification()
    {
        Livewire::test('informations.create')
            ->set('title', $this->newInfoTitle)
            ->set('roles.User', true)
            ->set('roles.Principal', true)
            ->set('roles.Vendor', true)
            ->set('roles.Manager', true)
            ->set('file', $this->fakeFile)
            ->call('addInformation');

        $this->assertDatabaseHas('informations', [
            'title' => $this->newInfoTitle,
            'file' => 'pdf.pdf',
            'creator_id' => $this->manager1->id,
            'organization_id' => $this->org1->id,
            'roles' => '["User","Manager","Principal","Vendor"]',
        ]);

        $this->assertDatabaseHas('custom_jobs', [
            'auth_id' => $this->manager1->id,
            'related_type' => Information::class,
            'action' => InformationAddedNotification::class,
        ]);

        $customJob = CustomJob::first();
        $userIds = $customJob->user_ids;

        $this->assertContains($this->parent1->id, $userIds);
        $this->assertContains($this->principal1->id, $userIds);
        $this->assertContains($this->vendor1->id, $userIds);
        $this->assertContains($this->manager2->id, $userIds);

        $this->assertNotContains($this->user1->id, $userIds);
        $this->assertNotContains($this->manager1->id, $userIds);

        $this->assertNotContains($this->manager3->id, $userIds);
        $this->assertNotContains($this->principal2->id, $userIds);
        $this->assertNotContains($this->user2->id, $userIds);
        $this->assertNotContains($this->parent2->id, $userIds);
        $this->assertNotContains($this->vendor2->id, $userIds);
    }
}
