<?php

namespace Tests\Feature\OpeningTimes;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OpeningTimesIndexTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    /** @test */
    public function opening_times_index_page_is_needs_user_login()
    {
        $this->get(route('openingtimes.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function user_role_cannot_view_opening_times_index_page()
    {
        $user = $this->getUser();

        $this->be($user);

        $this->get(route('openingtimes.index'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function principal_role_cannot_view_opening_times_index_page()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Principal', $org->id);

        $this->be($user);

        $this->get(route('openingtimes.index'))
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function manager_can_see_opening_times_index_page()
    {
        $org = Organization::factory()->create();
        $user = $this->getUser('Manager', $org->id);

        $this->be($user);

        $this->get(route('openingtimes.index'))
            ->assertSuccessful()
            ->assertSeeTextInOrder([
                trans('openingtimes.subtitle_opening_time'),
                $this->getWeekShortName(0),
                $this->getWeekShortName(1),
                $this->getWeekShortName(2),
                $this->getWeekShortName(3),
                $this->getWeekShortName(4),
                trans('openingtimes.subtitle_time_limitaitons'),
                trans('openingtimes.lead_label'),
                trans('openingtimes.selection_label'),
                trans('openingtimes.update'),
            ]);
    }

    private function getWeekShortName($key)
    {
        return now()->startOfWeek()
            ->addDays($key)
            ->shortDayName;
    }
}
