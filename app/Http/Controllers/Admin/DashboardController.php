<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\QrCode;
use App\Models\Submission;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = Cache::remember('dashboard_stats', 300, fn () => [
            'total_institutions' => Institution::count(),
            'active_qr_codes'    => QrCode::where('status', 'active')->count(),
            'pending_submissions' => Submission::where('status', 'pending')->count(),
            'approved_this_month' => Submission::where('status', 'approved')
                ->whereMonth('reviewed_at', now()->month)
                ->count(),
        ]);

        $byCategory = Cache::remember('dashboard_by_category', 300, fn () =>
            Institution::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->orderBy('count', 'desc')
                ->pluck('count', 'category')
        );

        $byState = Cache::remember('dashboard_by_state', 300, fn () =>
            Institution::selectRaw('state, count(*) as count')
                ->groupBy('state')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'state')
        );

        $recentSubmissions = Submission::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'institution_name', 'institution_category', 'institution_state', 'status', 'created_at', 'submitter_email']);

        $recentInstitutions = Institution::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'category', 'state', 'created_at', 'slug']);

        return Inertia::render('Admin/Dashboard', [
            'stats'              => $stats,
            'byCategory'         => $byCategory,
            'byState'            => $byState,
            'recentSubmissions'  => $recentSubmissions,
            'recentInstitutions' => $recentInstitutions,
        ]);
    }
}
