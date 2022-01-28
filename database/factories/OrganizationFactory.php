<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganizationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'name' => $this->faker->company,
            'street' => $this->faker->streetName,
            'house_no' => $this->faker->streetSuffix,
            'zip_code' => $this->faker->postcode,
            'city' => $this->faker->city,
        ];
    }
}
