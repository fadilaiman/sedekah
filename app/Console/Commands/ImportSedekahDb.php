<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Console\Command;
use PDO;

class ImportSedekahDb extends Command
{
    protected $signature = 'import:sedekah-db {--dry-run : Preview without writing to database}';
    protected $description = 'Import institutions from legacy docs/sedekah.db SQLite database';

    public function handle(): int
    {
        $dbPath = base_path('docs/sedekah.db');

        if (! file_exists($dbPath)) {
            $this->error("Legacy database not found at: {$dbPath}");
            return self::FAILURE;
        }

        $pdo = new PDO("sqlite:{$dbPath}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Discover tables in the legacy DB
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
        $this->info("Tables found: " . implode(', ', $tables));

        if (! in_array('institutions', $tables)) {
            $this->error("No 'institutions' table found in legacy database.");
            $this->info("Available tables: " . implode(', ', $tables));
            return self::FAILURE;
        }

        $rows = $pdo->query('SELECT * FROM institutions')->fetchAll(PDO::FETCH_ASSOC);
        $this->info("Found {$this->countRows($rows)} institution records.");

        if ($this->option('dry-run')) {
            $this->table(
                ['name', 'category', 'state', 'city'],
                array_slice(array_map(fn ($r) => [
                    $r['name'] ?? '?',
                    $r['category'] ?? '?',
                    $r['state'] ?? '?',
                    $r['city'] ?? '?',
                ], $rows), 0, 10)
            );
            $this->info('Dry run complete. No data written.');
            return self::SUCCESS;
        }

        $imported = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            $name = $row['name'] ?? null;
            $state = $row['state'] ?? null;

            if (! $name || ! $state) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $institution = Institution::firstOrCreate(
                ['name' => $name, 'state' => $state],
                [
                    'category' => $row['category'] ?? 'other',
                    'city' => $row['city'] ?? $state,
                    'address' => $row['address'] ?? null,
                    'description' => $row['description'] ?? null,
                    'website_url' => $row['website_url'] ?? null,
                    'contact_email' => $row['contact_email'] ?? null,
                    'contact_phone' => $row['contact_phone'] ?? null,
                    'lat' => isset($row['lat']) && is_numeric($row['lat']) ? $row['lat'] : null,
                    'lng' => isset($row['lng']) && is_numeric($row['lng']) ? $row['lng'] : null,
                    'maps_url' => $row['maps_url'] ?? null,
                    'coords_source' => $row['coords_source'] ?? null,
                    'scraped_at' => isset($row['scraped_at']) ? $row['scraped_at'] : null,
                ]
            );

            // Import QR codes if present in legacy DB
            if (! empty($row['qr_image_url']) && in_array('qr_codes', $tables)) {
                $this->importQrCodes($pdo, $institution, $row['id'] ?? null);
            }

            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Import complete: {$imported} institutions imported, {$skipped} skipped.");

        return self::SUCCESS;
    }

    private function countRows(array $rows): int
    {
        return count($rows);
    }

    private function importQrCodes(PDO $pdo, Institution $institution, ?int $legacyId): void
    {
        if (! $legacyId) {
            return;
        }

        $qrRows = $pdo->prepare('SELECT * FROM qr_codes WHERE institution_id = ?');
        $qrRows->execute([$legacyId]);
        $qrRows = $qrRows->fetchAll(PDO::FETCH_ASSOC);

        foreach ($qrRows as $qr) {
            $paymentMethodCode = $qr['payment_method_code'] ?? $qr['type'] ?? null;
            if (! $paymentMethodCode) {
                continue;
            }

            $paymentMethod = PaymentMethod::where('code', $paymentMethodCode)->first();
            if (! $paymentMethod) {
                continue;
            }

            QrCode::firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'payment_method_id' => $paymentMethod->id,
                ],
                [
                    'qr_image_url' => $qr['qr_image_url'] ?? '',
                    'qr_content' => $qr['qr_content'] ?? $qr['qr_image_url'] ?? '',
                    'status' => $qr['status'] ?? 'active',
                ]
            );
        }
    }
}
