<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Schedule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $scheduleTimes = [
            ['08:00', '11:00'],
            ['08:00', '12:00'],
            ['08:00', '13:00'],
            ['08:00', '15:00'],
            ['09:00', '12:00'],
            ['09:00', '13:00'],
            ['09:00', '14:00'],
            ['09:00', '16:00'],
            ['10:00', '14:00'],
            ['10:00', '12:00'],
            ['10:00', '13:00'],
            ['10:00', '15:00'],
            ['10:00', '17:00'],
            ['11:00', '15:00'],
            ['11:00', '16:00'],
            ['12:00', '16:00'],
            ['12:00', '17:00'],
            ['12:00', '18:00'],
        ];

        $randomArr = $scheduleTimes[array_rand($scheduleTimes)];
        // $randStatus = ['pending', 'approved', 'declined'];

        return [
            'uuid' => $this->faker->uuid,
            'date' => now()->addDays(rand(-20, -1))->format('Y-m-d'),
            // 'presence_start' => $randomArr[0],
            // 'presence_end' => $randomArr[1],
            // 'start' => $randomArr[0],
            // 'end' => $randomArr[1],
            // 'status' => $randStatus[array_rand($randStatus)],
            'status' => 'approved',
            'available' => true,
            'eats_onsite' => [
                'breakfast' => ! ! random_int(0, 1),
                'lunch' => ! ! random_int(0, 1),
                'dinner' => ! ! random_int(0, 1),
            ],
        ];
    }
}
