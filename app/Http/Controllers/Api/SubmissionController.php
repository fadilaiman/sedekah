<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionRequest;
use App\Models\Institution;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    // POST /api/v1/submissions
    public function store(SubmissionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle QR image upload
        $qrImageUrl = null;
        if ($request->hasFile('qr_image')) {
            $path = $request->file('qr_image')->store('submissions/qr', 'public');
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
