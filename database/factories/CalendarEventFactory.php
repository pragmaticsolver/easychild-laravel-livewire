<?php

namespace Database\Factories;

use App\Models\CalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CalendarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = now()->addDays(random_int(2, 20));

        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph(),
            'all_day' => $this->faker->randomElement([true, false]),
            'from' => $date,
            'to' => $date->copy()->addDays(random_int(2, 5)),
            'color' => $this->faker->randomElement(config('setting.events.colors')),
            'groups' => $this->faker->randomElements([1, 2, 3], 2),
            'files' => null,
            'organization_id' => 1,
            'creator_id' => random_int(1, 10),
        ];
    }
}
