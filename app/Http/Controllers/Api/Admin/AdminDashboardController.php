<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    // GET /api/v1/admin/dashboard/analytics
    public function analytics(): JsonResponse
    {
        $stats = [
            'total_institutions' => Institution::count(),
            'verified_institutions' => Institution::verified()->count(),
            'pending_submissions' => Submission::pending()->count(),
            'institutions_by_category' => Institution::selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->orderByDesc('count')
                ->pluck('count', 'category'),
            'institutions_by_state' => Institution::selectRaw('state, count(*) as count')
                ->groupBy('state')
                ->orderByDesc('count')
                ->pluck('count', 'state'),
        ];

        return response()->json($stats);
    }
}
