<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_categories', function (Blueprint $table) {
            $table->id();
            $table->string('value', 100)->unique();    // immutable slug, stored in institutions.category
            $table->string('label', 100);              // display name e.g. "Masjid"
            $table->string('icon', 100);               // Material Icons name e.g. "mosque"
            $table->string('color', 50)->default('gray'); // color key: green|blue|amber|pink|red|purple|orange|gray
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_categories');
    }
};
