<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedInstitution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminFeaturedController extends Controller
{
    // GET /api/v1/admin/featured
    public function index(): JsonResponse
    {
        $featured = FeaturedInstitution::with('institution')
            ->ordered()
            ->get();

        return response()->json($featured);
    }

    // PUT /api/v1/admin/featured — Replace all featured with new ordered list
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'institution_ids' => ['required', 'array', 'max:8'],
            'institution_ids.*' => ['exists:institutions,id'],
        ]);

        FeaturedInstitution::truncate();

        $featured = collect($request->institution_ids)
            ->unique()
            ->values()
            ->map(fn ($id, $order) => [
                'institution_id' => $id,
                'order' => $order + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->toArray();

        FeaturedInstitution::insert($featured);

        return response()->json(['message' => 'Featured institutions updated.']);
    }
}
