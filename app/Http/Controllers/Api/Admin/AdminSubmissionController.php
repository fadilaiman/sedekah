<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSubmissionController extends Controller
{
    // GET /api/v1/admin/submissions
    public function index(Request $request): JsonResponse
    {
        $query = Submission::with(['paymentMethod', 'reviewer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));

        return response()->json(
            $query->latest()->paginate($request->get('per_page', 20))
        );
    }

    // GET /api/v1/admin/submissions/{id}
    public function show(Submission $submission): JsonResponse
    {
        $submission->load(['paymentMethod', 'reviewer']);

        // Check for potential duplicates
        $duplicates = Institution::where('name', 'like', '%' . $submission->institution_name . '%')
            ->orWhere(function ($q) use ($submission) {
                $q->where('state', $submission->institution_state)
                  ->where('city', $submission->institution_city);
            })
            ->limit(5)
            ->get(['id', 'name', 'state', 'city', 'category']);

        return response()->json([
            'submission' => $submission,
            'potential_duplicates' => $duplicates,
        ]);
    }

    // POST /api/v1/admin/submissions/{id}/approve
    public function approve(Submission $submission): JsonResponse
    {
        if ($submission->status !== 'pending') {
            return response()->json(['message' => 'Submission is not pending.'], 422);
        }

        // Create the institution from submission data
        $institution = Institution::create([
            'name' => $submission->institution_name,
            'category' => $submission->institution_category,
            'state' => $submission->institution_state,
            'city' => $submission->institution_city,
            'address' => $submission->institution_address,
            'description' => $submission->institution_description,
            'website_url' => $submission->institution_website_url,
            'contact_email' => $submission->institution_contact_email,
            'contact_phone' => $submission->institution_contact_phone,
            'maps_url' => $submission->institution_maps_url,
        ]);

        // Create QR code if image provided
        if ($submission->qr_image_url && $submission->payment_method_id) {
            $institution->qrCodes()->create([
                'payment_method_id' => $submission->payment_method_id,
                'qr_image_url' => $submission->qr_image_url,
                'qr_content' => $submission->qr_image_url,
                'status' => 'active',
            ]);
        }

        $submission->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Submission approved.',
            'institution' => $institution,
        ]);
    }

    // POST /api/v1/admin/submissions/{id}/reject
    public function reject(Request $request, Submission $submission): JsonResponse
    {
        if ($submission->status !== 'pending') {
            return response()->json(['message' => 'Submission is not pending.'], 422);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return response()->json(['message' => 'Submission rejected.']);
    }
}
