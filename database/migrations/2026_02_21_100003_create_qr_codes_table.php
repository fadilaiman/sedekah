<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->restrictOnDelete();
            $table->string('qr_image_url', 500);
            $table->longText('qr_content');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->decimal('expected_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->unique(['institution_id', 'payment_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
