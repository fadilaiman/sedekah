<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submitter_email');
            $table->string('submitter_name')->nullable();

            // Submitted institution data
            $table->string('institution_name');
            $table->string('institution_category', 100);
            $table->string('institution_state', 100);
            $table->string('institution_city', 100);
            $table->string('institution_address', 500)->nullable();
            $table->longText('institution_description')->nullable();
            $table->string('institution_website_url', 500)->nullable();
            $table->string('institution_contact_email')->nullable();
            $table->string('institution_contact_phone', 20)->nullable();
            $table->string('institution_maps_url', 500)->nullable();

            // QR image upload
            $table->string('qr_image_url', 500)->nullable();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();

            // Review
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('submitter_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
