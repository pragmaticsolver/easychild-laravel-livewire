<?php

namespace Tests\Commands;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\TestTime\TestTime;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class UserLogClearCommandTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->org, $this->group, $this->user] = $this->getOrgGroupUser();
    }

    /** @test */
    public function clears_user_logs_older_than_six_months()
    {
        TestTime::freeze();

        $this->createUserLog($this->user, 'enter');
        $this->createUserLog($this->user, 'leave');

        TestTime::addMonths(8);

        $this->artisan('user-logs:clear');

        $this->assertDatabaseCount('user_logs', 0);
    }

    /** @test */
    public function clears_user_logs_only_older_than_six_months()
    {
        TestTime::freeze();
        TestTime::addMonths(4);
        $this->createUserLog($this->user, 'enter');

        TestTime::subMonths(4);
        $this->createUserLog($this->user, 'leave');
        $this->createUserLog($this->user, 'leave');

        TestTime::addMonths(8);

        $this->artisan('user-logs:clear');

        $this->assertDatabaseCount('user_logs', 1);
    }

    /** @test */
    public function clears_user_logs_will_not_clear_logs_newer_than_six_months()
    {
        TestTime::freeze();
        $this->createUserLog($this->user, 'enter');

        $this->createUserLog($this->user, 'leave');
        $this->createUserLog($this->user, 'leave');

        TestTime::addMonths(4);

        $this->artisan('user-logs:clear');

        $this->assertDatabaseCount('user_logs', 3);
    }
}
