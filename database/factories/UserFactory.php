<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    protected $roles = ['Principal', 'User'];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $role = $this->roles[array_rand($this->roles)];

        $dob = $this->faker->dateTimeBetween('-13 years', '-7 years');

        $dob = Carbon::parse($dob);
        $dob->setMonth(now()->month);

        return [
            'uuid' => $this->faker->uuid,
            'given_names' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => null,
            'password' => 'password',
            'remember_token' => null,
            'token' => Str::random(99),
            'role' => $role,
            'organization_id' => $role == 'Admin' ? null : rand(1, 30),
            'dob' => $dob,
        ];
    }
}
