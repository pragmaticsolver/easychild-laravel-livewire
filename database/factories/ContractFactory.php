<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $times = [
            ['08:00', '14:00'],
            ['09:00', '15:00'],
            ['08:30', '15:30'],
            ['09:30', '16:00'],
            ['10:00', '13:30'],
            ['10:00', null],
            ['09:00', null],
            ['08:00', null],
        ];

        $time = $this->faker->randomElement($times);

        return [
            'title' => $this->faker->text(10),
            'time_per_day' => random_int(3, 6),
            'bring_until' => $time[0],
            'collect_until' => $time[1],
            'overtime' => random_int(0, 3),
            'organization_id' => Organization::factory(),
        ];
    }
}
