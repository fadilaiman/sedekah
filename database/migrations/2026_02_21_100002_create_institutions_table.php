<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category', 100);
            $table->string('state', 100);
            $table->string('city', 100);
            $table->string('address', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('external_campaign_url', 500)->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('maps_url', 500)->nullable();
            $table->string('coords_source', 100)->nullable();
            $table->unsignedBigInteger('logo_image_id')->nullable();
            $table->unsignedBigInteger('banner_image_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('scraped_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('state');
            $table->index('city');
            $table->index('name');
            $table->index('verified_at');
        });

        // FULLTEXT and SPATIAL indexes only for MySQL/MariaDB
        if (config('database.default') !== 'sqlite') {
            Schema::table('institutions', function (Blueprint $table) {
                $table->fullText(['name', 'description']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
