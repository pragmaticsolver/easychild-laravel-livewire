<?php

namespace Tests\Feature\OpeningTimes;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\TestHelpersTraits;

class OpeningTimeItemTest extends TestCase
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
    public function start_time_is_required_for_updating_opening_times()
    {
        $openingTimes = collect($this->org->settings['opening_times']);
        $key = 1;

        $openingTime = $openingTimes->where('key', $key)->first();

        Livewire::test('opening-times.item', compact('openingTime'))
            ->set('start', '')
            ->set('end', '09:00')
            ->call('saveOpeningTime')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.start_required'),
            ]);
    }

    /** @test */
    public function end_time_is_required_for_updating_opening_times()
    {
        $openingTimes = collect($this->org->settings['opening_times']);
        $key = 1;

        $openingTime = $openingTimes->where('key', $key)->first();

        Livewire::test('opening-times.item', compact('openingTime'))
            ->set('start', '09:00')
            ->set('end', '')
            ->call('saveOpeningTime')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.end_required'),
            ]);
    }

    /** @test */
    public function start_time_should_be_before_end_time()
    {
        $openingTimes = collect($this->org->settings['opening_times']);
        $key = 1;

        $openingTime = $openingTimes->where('key', $key)->first();

        Livewire::test('opening-times.item', compact('openingTime'))
            ->set('start', '09:00')
            ->set('end', '09:00')
            ->call('saveOpeningTime')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.start_less_than_end'),
            ]);

        Livewire::test('opening-times.item', compact('openingTime'))
            ->set('start', '19:00')
            ->set('end', '09:00')
            ->call('saveOpeningTime')
            ->assertEmitted('server-message', [
                'type' => 'error',
                'message' => trans('openingtimes.start_less_than_end'),
            ]);
    }

    /** @test */
    public function test_each_days_short_form_and_default_opening_times()
    {
        $openingTimes = collect($this->org->settings['opening_times']);

        for ($x = 0; $x < 5; $x++) {
            $shortName = now()->startOfWeek()
                ->addDays($x)
                ->shortDayName;
            $openingTime = $openingTimes->where('key', $x)->first();

            Livewire::test('opening-times.item', compact('openingTime'))
                ->assertSet('start', $openingTime['start'])
                ->assertSet('end', $openingTime['end'])
                ->assertSeeText($shortName);
        }
    }

    /** @test */
    public function check_opening_times_can_be_adjusted_for_each_week_day()
    {
        $openingTimes = collect($this->org->settings['opening_times']);

        for ($key = 0; $key < 5; $key++) {
            $openingTime = $openingTimes->where('key', $key)->first();

            Livewire::test('opening-times.item', compact('openingTime'))
                ->assertSet('start', $openingTime['start'])
                ->assertSet('end', $openingTime['end'])
                ->set('start', '08:00')
                ->set('end', '09:00')
                ->call('saveOpeningTime');

            $openingTimes = collect($this->org->fresh()->settings['opening_times']);
            $oldOpeningTime = $openingTimes->where('key', $key)->first();

            $newOpeningTime = [
                'key' => $key,
                'start' => '08:00',
                'end' => '09:00',
            ];

            $this->assertEquals($newOpeningTime, $oldOpeningTime);
        }
    }

    /** @test */
    public function check_opening_times_can_be_adjusted_for_each_week_day_by_alpine_call()
    {
        $openingTimes = collect($this->org->settings['opening_times']);

        for ($key = 0; $key < 5; $key++) {
            $openingTime = $openingTimes->where('key', $key)->first();

            Livewire::test('opening-times.item', compact('openingTime'))
                ->assertSet('start', $openingTime['start'])
                ->assertSet('end', $openingTime['end'])
                ->call('saveOpeningTime', [
                    'start' => '08:00',
                    'end' => '09:00',
                ])
                ->assertEmitted('server-message', [
                    'type' => 'success',
                    'message' => trans('openingtimes.create_success', [
                        'day' => now()->startOfWeek()
                            ->addDays($key)
                            ->dayName,
                    ]),
                ]);

            $openingTimes = collect($this->org->fresh()->settings['opening_times']);
            $oldOpeningTime = $openingTimes->where('key', $key)->first();

            $newOpeningTime = [
                'key' => $key,
                'start' => '08:00',
                'end' => '09:00',
            ];

            $this->assertEquals($newOpeningTime, $oldOpeningTime);
        }
    }
}
