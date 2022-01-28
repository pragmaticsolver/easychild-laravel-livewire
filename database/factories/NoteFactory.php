<?php

namespace Database\Factories;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $users = User::where('role')
            ->pluck('id')
            ->toArray();

        return [
            'user_id' => $this->faker->randomElement($users),
            'title' => $this->faker->sentence(5),
            'text' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'normal', 'urgent']),
        ];
    }
}
