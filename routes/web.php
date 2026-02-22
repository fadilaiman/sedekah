<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FeaturedController;
use App\Http\Controllers\Admin\InstitutionController as AdminInstitutionController;
use App\Http\Controllers\Admin\PaymentMethodController as AdminPaymentMethodController;
use App\Http\Controllers\Admin\SubmissionController as AdminSubmissionController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Models\FeaturedInstitution;
use App\Models\Institution;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Public homepage ───────────────────────────────────────────────────────────
Route::get('/', function () {
    $featured = Cache::remember('homepage_featured', 600, function () {
        return FeaturedInstitution::with([
            'institution' => fn ($q) => $q->with(['activeQrCodes.paymentMethod']),
        ])->ordered()->get()->pluck('institution')->filter()->values();
    });

    $stats = Cache::remember('homepage_stats', 300, fn () => [
        'total' => Institution::count(),
        'by_category' => Institution::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category'),
    ]);

    return Inertia::render('Welcome', [
        'featured' => $featured,
        'stats' => $stats,
    ]);
})->name('home');

// ── Admin authentication (magic-link, no password) ────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    // Development only: Quick login without email (only when APP_DEBUG=true)
    if (config('app.debug')) {
        Route::get('/dev-login', function () {
            $admins = \App\Models\User::whereIn('role', ['admin', 'super_admin'])
                ->select('id', 'email', 'name', 'role')
                ->get();

            return \Inertia\Inertia::render('Auth/DevLogin', ['admins' => $admins]);
        })->name('dev-login');

        Route::post('/dev-login', function (\Illuminate\Http\Request $request) {
            $user = \App\Models\User::find($request->user_id);

            if (!$user || !$user->isAdmin()) {
                return back()->withErrors(['user_id' => 'Invalid user or not an admin']);
            }

            \Illuminate\Support\Facades\Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard');
        })->name('dev-login.submit');
    }

    Route::get('/login', [MagicLinkController::class, 'showLoginForm'])
        ->middleware('guest')
        ->name('login');

    Route::post('/login', [MagicLinkController::class, 'sendLink'])
        ->middleware('throttle:5,15')
        ->name('login.send');

    Route::get('/login/verify/{token}', [MagicLinkController::class, 'verifyToken'])
        ->name('login.verify');

    Route::post('/logout', [MagicLinkController::class, 'logout'])
        ->name('logout');

    // Admin pages (admin middleware handles auth + role)
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Institution CRUD
        Route::get('/institutions', [AdminInstitutionController::class, 'index'])->name('institutions.index');
        Route::get('/institutions/create', [AdminInstitutionController::class, 'create'])->name('institutions.create');
        Route::post('/institutions', [AdminInstitutionController::class, 'store'])->name('institutions.store');
        Route::get('/institutions/{institution}/edit', [AdminInstitutionController::class, 'edit'])->name('institutions.edit');
        Route::put('/institutions/{institution}', [AdminInstitutionController::class, 'update'])->name('institutions.update');
        Route::delete('/institutions/{institution}', [AdminInstitutionController::class, 'destroy'])->name('institutions.destroy');

        // QR code management
        Route::post('/institutions/{institution}/qr-codes', [AdminInstitutionController::class, 'storeQr'])->name('institutions.qr.store');
        Route::delete('/institutions/{institution}/qr-codes/{qrCode}', [AdminInstitutionController::class, 'destroyQr'])->name('institutions.qr.destroy');
        Route::patch('/institutions/{institution}/qr-codes/{qrCode}/toggle', [AdminInstitutionController::class, 'toggleQrStatus'])->name('institutions.qr.toggle');

        // Submissions
        Route::get('/submissions', [AdminSubmissionController::class, 'index'])->name('submissions.index');
        Route::post('/submissions/{submission}/approve', [AdminSubmissionController::class, 'approve'])->name('submissions.approve');
        Route::post('/submissions/{submission}/reject', [AdminSubmissionController::class, 'reject'])->name('submissions.reject');

        // Featured
        Route::get('/featured', [FeaturedController::class, 'edit'])->name('featured.edit');
        Route::post('/featured', [FeaturedController::class, 'update'])->name('featured.update');

        // Categories (reorder must come before {category} to avoid model binding)
        Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Payment Methods
        Route::get('/payment-methods', [AdminPaymentMethodController::class, 'index'])->name('payment-methods.index');
        Route::post('/payment-methods', [AdminPaymentMethodController::class, 'store'])->name('payment-methods.store');
        Route::put('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'update'])->name('payment-methods.update');
        Route::delete('/payment-methods/{paymentMethod}', [AdminPaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');
    });
});

// ── Public institution pages ──────────────────────────────────────────────────
Route::get('/institutions', function () {
    return Inertia::render('Institution/Index');
})->name('institutions.index');

// ── Community submission form ─────────────────────────────────────────────────
Route::get('/submit', function () {
    return Inertia::render('Submit/Index');
})->name('submit');

Route::get('/submit/thank-you', function () {
    return Inertia::render('Submit/ThankYou');
})->name('submit.thank-you');

// ── Institution detail (must be last — catches /{slug}) ───────────────────────
Route::get('/{slug}', function ($slug) {
    $institution = Institution::where('slug', $slug)
        ->with(['qrCodes' => fn ($q) => $q->where('status', 'active'), 'qrCodes.paymentMethod'])
        ->firstOrFail();

    return Inertia::render('Institution/Show', [
        'institution' => $institution,
    ]);
})->name('institutions.show');
