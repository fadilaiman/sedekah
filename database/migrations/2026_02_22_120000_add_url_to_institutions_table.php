<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add donation URL column to institutions table.
     * Supports any donation/payment platform (ToyyibPay, iPay88, etc.)
     */
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('url', 500)->nullable()->after('external_campaign_url');
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex(['url']);
            $table->dropColumn('url');
        });
    }
};
