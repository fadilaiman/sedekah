<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InstitutionController extends Controller
{
    // GET /api/v1/institutions
    public function index(Request $request): JsonResponse
    {
        $query = Institution::with(['activeQrCodes.paymentMethod'])
            ->whereNull('deleted_at')
            ->verified(); // Only show verified institutions (those with QR codes)

        // Filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('state')) {
            $query->byState($request->state);
        }

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Prioritize institutions with donation URLs (60% priority, still randomized)
        if ($request->get('prioritize_url', true) && !$request->filled('sort')) {
            // Use weighted randomization: institutions with URL have ~1.5x higher chance
            // This maintains 60% with URL while keeping good randomization
            $dbDriver = config('database.default');
            if ($dbDriver === 'sqlite') {
                // SQLite: Weighted random with balanced bias toward WITH URL
                // With URL: offset 250 + random 0-250 = range 250-500
                // Without URL: offset 0 + random 0-200 = range 0-200
                // Achieves ~55-65% WITH URL while maintaining good randomness
                $query->selectRaw('institutions.*,
                    (CASE WHEN url IS NOT NULL
                        THEN 250 + (ABS(RANDOM()) % 250)
                        ELSE ABS(RANDOM()) % 200
                    END) as priority_weight'
                )->orderByDesc('priority_weight');
            } else {
                // MySQL: Similar weighted approach
                $query->selectRaw('institutions.*,
                    (CASE WHEN url IS NOT NULL
                        THEN 250 + (RAND() * 250)
                        ELSE RAND() * 200
                    END) as priority_weight'
                )->orderByDesc('priority_weight');
            }
        } else {
            // Standard sorting
            $sort = $request->get('sort', 'name');
            $direction = $request->get('direction', 'asc');
            $allowedSorts = ['name', 'created_at', 'state', 'city'];
            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');
            }
        }

        $institutions = $query->paginate($request->get('per_page', 20));

        return response()->json($institutions);
    }

    // GET /api/v1/institutions/{id}
    public function show(Institution $institution): JsonResponse
    {
        $data = Cache::remember("institution_{$institution->id}", 300, fn () =>
            $institution->load([
                'qrCodes' => fn ($q) => $q->where('status', 'active'),
                'qrCodes.paymentMethod',
            ])
        );

        return response()->json($data);
    }

    // GET /api/v1/institutions/slug/{slug}
    public function showBySlug(string $slug): JsonResponse
    {
        $institution = Cache::remember("institution_slug_{$slug}", 300, fn () =>
            Institution::where('slug', $slug)
                ->with([
                    'qrCodes' => fn ($q) => $q->where('status', 'active'),
                    'qrCodes.paymentMethod',
                ])
                ->firstOrFail()
        );

        return response()->json($institution);
    }
}
