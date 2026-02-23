<?php

use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminInstitutionController;
use App\Http\Controllers\Api\Admin\AdminQrCodeController;
use App\Http\Controllers\Api\Admin\AdminSubmissionController;
use App\Http\Controllers\Api\Admin\AdminFeaturedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Sedekah.online v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // ── Public endpoints (no auth) ──────────────────────────────────────

    Route::middleware('throttle:15,1')->group(function () {
        // Institutions
        Route::get('/institutions', [InstitutionController::class, 'index']);
        Route::get('/institutions/{institution}', [InstitutionController::class, 'show'])->middleware('track.visit');
        Route::get('/institutions/slug/{slug}', [InstitutionController::class, 'showBySlug']);

        // QR Code downloads
        Route::get('/institutions/{institution}/qr-codes/{qrCode}/download', [InstitutionController::class, 'downloadQrCode']);

        // Payment methods
        Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

        // Community submission (public, separate rate limit)
        Route::get('/submissions/check-duplicate', [\App\Http\Controllers\Api\SubmissionController::class, 'checkDuplicate']);
        Route::post('/submissions', [\App\Http\Controllers\Api\SubmissionController::class, 'store'])
            ->middleware('throttle:5,60');
    });

    // ── Admin endpoints (auth required) ────────────────────────────────

    Route::middleware(['auth:sanctum', 'admin', 'throttle:60,1'])->prefix('admin')->name('admin.api.')->group(function () {

        // Dashboard analytics
        Route::get('/dashboard/analytics', [AdminDashboardController::class, 'analytics']);

        // Institution CRUD
        Route::apiResource('institutions', AdminInstitutionController::class);

        // QR Code management
        Route::post('/institutions/{institution}/qr-codes', [AdminQrCodeController::class, 'store']);
        Route::patch('/institutions/{institution}/qr-codes/{qrCode}', [AdminQrCodeController::class, 'update']);
        Route::delete('/institutions/{institution}/qr-codes/{qrCode}', [AdminQrCodeController::class, 'destroy']);

        // Submissions
        Route::get('/submissions', [AdminSubmissionController::class, 'index']);
        Route::get('/submissions/{submission}', [AdminSubmissionController::class, 'show']);
        Route::post('/submissions/{submission}/approve', [AdminSubmissionController::class, 'approve']);
        Route::post('/submissions/{submission}/reject', [AdminSubmissionController::class, 'reject']);

        // Featured institutions
        Route::get('/featured', [AdminFeaturedController::class, 'index']);
        Route::put('/featured', [AdminFeaturedController::class, 'update']);

        // CSV import
        Route::post('/institutions/import', [AdminInstitutionController::class, 'import']);
    });

});
