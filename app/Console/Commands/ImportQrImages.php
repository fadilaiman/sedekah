<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PDO;

class ImportQrImages extends Command
{
    protected $signature = 'import:qr-images {--dry-run : Preview without writing}';
    protected $description = 'Copy QR images from docs/images/ and link them to institutions via legacy DB lookup';

    // Maps legacy category folder to our category values
    private array $categoryFolders = [
        'mosque' => 'mosque',
        'surau'  => 'surau',
        'others' => 'other', // All other legacy categories map to 'other'
    ];

    public function handle(): int
    {
        $imagesBase = base_path('docs/images');
        $legacyDb   = base_path('docs/sedekah.db');

        if (! is_dir($imagesBase)) {
            $this->error("Images directory not found: {$imagesBase}");
            return self::FAILURE;
        }

        if (! file_exists($legacyDb)) {
            $this->error("Legacy database not found: {$legacyDb}");
            return self::FAILURE;
        }

        $pdo = new PDO("sqlite:{$legacyDb}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Load legacy institution → payment mapping
        $paymentMap = [];
        $stmt = $pdo->query('SELECT institution_id, payment_code FROM institution_payments ORDER BY institution_id, payment_code');
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (! isset($paymentMap[$row['institution_id']])) {
                $paymentMap[$row['institution_id']] = $row['payment_code'];
            }
        }

        // Load legacy institutions with qr_image
        $legacyStmt = $pdo->query(
            'SELECT id, name, category, state, city, qr_image FROM institutions WHERE qr_image IS NOT NULL AND qr_image != \'\''
        );
        $legacyRows = $legacyStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->info("Found " . count($legacyRows) . " legacy institutions with QR images.");

        $matched = $skipped = $copied = 0;

        $bar = $this->output->createProgressBar(count($legacyRows));
        $bar->start();

        foreach ($legacyRows as $row) {
            $legacyId = $row['id'];
            $filename = $row['qr_image'];              // e.g. "4_Masjid Ar-Rahman.png"
            $category = $row['category'] ?? 'others';
            $folder   = $category === 'mosque' ? 'mosque' : ($category === 'surau' ? 'surau' : 'others');

            $sourcePath = "{$imagesBase}/{$folder}/{$filename}";

            if (! file_exists($sourcePath)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Find institution in our DB by name match
            $institution = Institution::where('name', $row['name'])->first()
                ?? Institution::where('name', 'like', '%' . substr($row['name'], 0, 20) . '%')->first();

            if (! $institution) {
                $skipped++;
                $bar->advance();
                continue;
            }

            // Skip if already has a QR code
            if ($institution->qrCodes()->exists()) {
                $skipped++;
                $bar->advance();
                $matched++;
                continue;
            }

            // Determine payment method
            $legacyCode    = $paymentMap[$legacyId] ?? 'duitnow';
            $paymentMethod = PaymentMethod::where('code', $legacyCode)->first()
                ?? PaymentMethod::where('code', 'duitnow')->first()
                ?? PaymentMethod::where('active', true)->first();

            if (! $paymentMethod) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if ($this->option('dry-run')) {
                $this->newLine();
                $this->line("  [{$institution->name}] → {$folder}/{$filename} ({$paymentMethod->name})");
                $matched++;
                $bar->advance();
                continue;
            }

            // Copy image to storage
            $destKey  = "qr-codes/{$folder}/{$filename}";
            $contents = file_get_contents($sourcePath);
            Storage::disk('public')->put($destKey, $contents);
            $copied++;

            // Create QrCode record
            QrCode::firstOrCreate(
                ['institution_id' => $institution->id, 'payment_method_id' => $paymentMethod->id],
                [
                    'qr_image_path' => $destKey,
                    'qr_image_url'  => Storage::url($destKey),
                    'status'        => 'active',
                ]
            );

            $matched++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done: {$matched} matched, {$copied} images copied, {$skipped} skipped.");

        return self::SUCCESS;
    }
}
