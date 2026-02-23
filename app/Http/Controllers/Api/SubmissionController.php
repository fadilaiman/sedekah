<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionRequest;
use App\Models\Institution;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    // GET /api/v1/submissions/check-duplicate
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:3',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $name = $request->input('name');
        $state = $request->input('state');
        $city = $request->input('city');

        // Search ALL institutions (not just verified) by name
        $institutions = Institution::where('name', 'like', "%{$name}%")
            ->when($state, fn ($q) => $q->where('state', $state))
            ->limit(5)
            ->get(['id', 'name', 'city', 'state', 'slug']);

        // Search pending submissions by name
        $pendingSubmissions = Submission::where('status', 'pending')
            ->where('institution_name', 'like', "%{$name}%")
            ->when($state, fn ($q) => $q->where('institution_state', $state))
            ->limit(3)
            ->get(['id', 'institution_name as name', 'institution_city as city', 'institution_state as state']);

        return response()->json([
            'institutions' => $institutions,
            'pending_submissions' => $pendingSubmissions,
        ]);
    }

    // POST /api/v1/submissions
    public function store(SubmissionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle QR image upload
        // Workaround: realpath() fails for temp files on Windows, so use file_get_contents
        $qrImageUrl = null;
        if ($request->hasFile('qr_image')) {
            $file = $request->file('qr_image');
            $path = 'submissions/qr/' . \Illuminate\Support\Str::random(40) . '.' . $file->guessExtension();
            Storage::disk('public')->put($path, file_get_contents($file->getPathname()));
            $qrImageUrl = Storage::url($path);
        }

        $submission = Submission::create([
            ...$data,
            'qr_image_url' => $qrImageUrl,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Submission received. An admin will review it shortly.',
            'id' => $submission->id,
        ], 201);
    }
}
