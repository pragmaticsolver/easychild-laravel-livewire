<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $now = now()->subWeek()->addDays($this->faker->randomElement([0, 1, 2, 3, 4, 5, 6]))
            ->addHours($this->faker->randomElement([-1, -2, -3, -4, -5, 0, 1, 2, 3, 4, 5, 6]));

        return [
            'conversation_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'sender_id' => $this->faker->randomElement([1, 2, 3, 4]),
            'body' => $this->faker->sentence(10),
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
