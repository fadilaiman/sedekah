<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'   => strtolower($this->faker->unique()->lexify('???')),
            'name'   => $this->faker->randomElement(['TnG', 'GrabPay', 'Boost', 'ShopeePay']),
            'active' => true,
        ];
    }
}
