<?php

namespace Tests\Feature\OpeningTimes;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class TimeLimitationsTest extends TestCase
{
    use RefreshDatabase, TestHelpersTraits;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Organization::factory()->create();
        $user = $this->getUser('Principal', $this->org->id);

        $this->be($user);
    }

    /** @test */
    public function principal_can_change_limitations_values()
    {
        Livewire::test('opening-times.limitation')
            ->set('leadTime', 10)
            ->set('selectionTime', 20)
            ->call('saveLimitations')
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('openingtimes.lead_selection_update'),
            ]);

        $expected = [
            'lead_time' => 10,
            'selection_time' => 20,
        ];

        $this->assertEquals($expected, $this->org->fresh()->settings['limitations']);
    }

    /** @test */
    public function principal_can_change_limitations_values_using_alpine_call()
    {
        Livewire::test('opening-times.limitation')
            ->call('saveLimitations', [
                'leadTime' => 10,
                'selectionTime' => 20,
            ])
            ->assertEmitted('server-message', [
                'type' => 'success',
                'message' => trans('openingtimes.lead_selection_update'),
            ]);

        $expected = [
            'lead_time' => 10,
            'selection_time' => 20,
        ];

        $this->assertEquals($expected, $this->org->fresh()->settings['limitations']);
    }

    /** @test */
    public function lead_time_is_required_when_submitting_time_limitations()
    {
        Livewire::test('opening-times.limitation')
            ->set('leadTime', '')
            ->set('selectionTime', 20)
            ->call('saveLimitations')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.lead_required'),
            ]);
    }

    /** @test */
    public function selection_time_is_required_when_submitting_time_limitations()
    {
        Livewire::test('opening-times.limitation')
            ->set('leadTime', 10)
            ->set('selectionTime', '')
            ->call('saveLimitations')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.selection_required'),
            ]);
    }

    /** @test */
    public function lead_time_should_have_a_min_value_validation()
    {
        Livewire::test('opening-times.limitation')
            ->set('leadTime', 1)
            ->set('selectionTime', 20)
            ->call('saveLimitations')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.lead_great_or_equal', ['value' => config('setting.minLeadDays')]),
            ]);
    }

    /** @test */
    public function selection_time_should_have_a_min_value_validation()
    {
        Livewire::test('opening-times.limitation')
            ->set('leadTime', 10)
            ->set('selectionTime', 1)
            ->call('saveLimitations')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.selection_great_or_equal', ['value' => config('setting.minSelectionDays')]),
            ]);
    }
}
