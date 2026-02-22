<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed categories
        $this->call(CategorySeeder::class);

        // Seed payment methods
        $this->call(PaymentMethodSeeder::class);

        // Seed featured institutions (from institutions with QR codes)
        $this->call(FeaturedInstitutionSeeder::class);

        // Create default super admin for development
        User::firstOrCreate(
            ['email' => 'admin@sedekah.info'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@sedekah.info',
                'password' => Hash::make('changeme'),
                'role' => 'super_admin',
            ]
        );
    }
}
