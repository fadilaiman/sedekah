<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class InstitutionDonationUrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeds institution donation URLs from matched permalink CSV data.
     * Supports any donation platform (ToyyibPay, iPay88, etc.)
     */
    public function run(): void
    {
        // Read exact matches from CSV
        $exactMatches = $this->readExactMatches();

        if (empty($exactMatches)) {
            $this->command->warn("No exact matches found in CSV file");
            return;
        }

        // Deduplicate by institution ID (keep first URL only, 1-to-1 mapping)
        $uniqueUrls = [];
        foreach ($exactMatches as $row) {
            if (!isset($uniqueUrls[$row['db_id']])) {
                $uniqueUrls[$row['db_id']] = $row['toyyibpay_url'];
            }
        }

        // Update institutions table with donation URLs
        $updated = 0;
        foreach ($uniqueUrls as $institutionId => $url) {
            DB::table('institutions')
                ->where('id', $institutionId)
                ->update(['url' => $url]);
            $updated++;
        }

        $this->command->info("Updated {$updated} institutions with donation URLs");
    }

    /**
     * Read exact matches from CSV file.
     */
    private function readExactMatches(): array
    {
        $csvPath = database_path('../docs/permalinks_for_db.csv');

        if (!File::exists($csvPath)) {
            throw new \Exception("CSV file not found: {$csvPath}");
        }

        $matches = [];
        $handle = fopen($csvPath, 'r');

        if (!$handle) {
            throw new \Exception("Cannot open CSV file: {$csvPath}");
        }

        // Read header
        $header = fgetcsv($handle);
        $headerMap = array_flip($header);

        // Read data rows
        while ($row = fgetcsv($handle)) {
            $data = array_combine($header, $row);

            // Only process exact matches (tier = 'exact')
            if ($data['match_tier'] === 'exact') {
                $matches[] = [
                    'db_id' => (int) $data['db_id'],
                    'db_name' => $data['db_name'],
                    'csv_name' => $data['csv_name'],
                    'toyyibpay_url' => $data['toyyibpay_url'],
                ];
            }
        }

        fclose($handle);

        return $matches;
    }
}
