<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark all institutions without active QR codes as unverified (verified_at = NULL)
        // This targets incomplete or old data entries that need QR code management
        DB::update('
            UPDATE institutions
            SET verified_at = NULL
            WHERE id NOT IN (
                SELECT DISTINCT institution_id FROM qr_codes WHERE status = ?
            )
        ', ['active']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This migration marks institutions as unverified (data transformation)
        // Rollback would require restoring verified_at values from backup
    }
};
