<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionRequest;
use App\Models\Institution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminInstitutionController extends Controller
{
    // GET /api/v1/admin/institutions
    public function index(Request $request): JsonResponse
    {
        $query = Institution::withTrashed()
            ->with(['activeQrCodes.paymentMethod'])
            ->when($request->filled('search'), fn ($q) => $q->search($request->search))
            ->when($request->filled('category'), fn ($q) => $q->byCategory($request->category))
            ->when($request->filled('state'), fn ($q) => $q->byState($request->state));

        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        $query->orderBy($sort, $direction);

        return response()->json($query->paginate($request->get('per_page', 25)));
    }

    // GET /api/v1/admin/institutions/{id}
    public function show(Institution $institution): JsonResponse
    {
        $institution->load(['qrCodes.paymentMethod']);

        return response()->json($institution);
    }

    // POST /api/v1/admin/institutions
    public function store(InstitutionRequest $request): JsonResponse
    {
        $institution = Institution::create($request->validated());

        return response()->json($institution, 201);
    }

    // PUT /api/v1/admin/institutions/{id}
    public function update(InstitutionRequest $request, Institution $institution): JsonResponse
    {
        $institution->update($request->validated());

        return response()->json($institution);
    }

    // DELETE /api/v1/admin/institutions/{id} — soft delete
    public function destroy(Institution $institution): JsonResponse
    {
        $institution->delete();

        return response()->json(['message' => 'Institution archived.']);
    }

    // POST /api/v1/admin/institutions/import — CSV import (placeholder)
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        // CSV import logic will be implemented in a queued job
        return response()->json(['message' => 'Import queued. Check back shortly.'], 202);
    }
}
