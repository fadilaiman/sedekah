<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'submitter_email'      => $this->faker->email(),
            'submitter_name'       => $this->faker->name(),
            'institution_name'     => $this->faker->company() . ' Masjid',
            'institution_category' => 'mosque',
            'institution_state'    => 'Selangor',
            'institution_city'     => 'Petaling Jaya',
            'status'               => 'pending',
        ];
    }
}
