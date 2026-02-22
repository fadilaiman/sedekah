<?php

namespace Database\Seeders;

use App\Models\FeaturedInstitution;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class FeaturedInstitutionSeeder extends Seeder
{
    /**
     * Seed featured institutions (up to 8) from institutions that have active QR codes
     */
    public function run(): void
    {
        // Only seed if featured_institutions table is empty
        if (FeaturedInstitution::count() > 0) {
            return;
        }

        // Get institutions with active QR codes, limit to 8
        $institutions = Institution::has('activeQrCodes')
            ->limit(8)
            ->get();

        $order = 1;
        foreach ($institutions as $institution) {
            FeaturedInstitution::create([
                'institution_id' => $institution->id,
                'order' => $order,
            ]);
            $order++;
        }

        $this->command->info("Featured {$institutions->count()} institutions.");
    }
}
