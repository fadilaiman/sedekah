<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InstitutionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'     => $this->faker->company() . ' ' . $this->faker->randomElement(['Masjid', 'Surau', 'Yayasan']),
            'category' => $this->faker->randomElement(['mosque', 'surau', 'other']),
            'state'    => $this->faker->randomElement(['Selangor', 'Johor', 'W.P. Kuala Lumpur', 'Perak', 'Kedah']),
            'city'     => $this->faker->city(),
            'address'  => $this->faker->address(),
        ];
    }
}
