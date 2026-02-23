<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\QrCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminQrCodeController extends Controller
{
    // POST /api/v1/admin/institutions/{institution}/qr-codes
    public function store(Request $request, Institution $institution): JsonResponse
    {
        $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'qr_image' => ['required', 'image', 'mimes:jpeg,png', 'max:100'], // 100 KB
            'qr_content' => ['required', 'string', 'max:2000'],
            'expected_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:active,inactive,pending'],
        ]);

        // Workaround: realpath() fails for temp files on Windows, so use file_get_contents
        $file = $request->file('qr_image');
        $path = 'qr_codes/' . \Illuminate\Support\Str::random(40) . '.' . $file->guessExtension();
        Storage::disk('public')->put($path, file_get_contents($file->getPathname()));

        $qrCode = $institution->qrCodes()->create([
            'payment_method_id' => $request->payment_method_id,
            'qr_image_url' => Storage::url($path),
            'qr_content' => $request->qr_content,
            'expected_amount' => $request->expected_amount,
            'status' => $request->get('status', 'active'),
        ]);

        return response()->json($qrCode->load('paymentMethod'), 201);
    }

    // PATCH /api/v1/admin/institutions/{institution}/qr-codes/{qrCode}
    public function update(Request $request, Institution $institution, QrCode $qrCode): JsonResponse
    {
        $request->validate([
            'status' => ['sometimes', 'in:active,inactive,pending'],
            'qr_content' => ['sometimes', 'string', 'max:2000'],
            'expected_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $qrCode->update($request->validated());

        return response()->json($qrCode->load('paymentMethod'));
    }

    // DELETE /api/v1/admin/institutions/{institution}/qr-codes/{qrCode}
    public function destroy(Institution $institution, QrCode $qrCode): JsonResponse
    {
        $qrCode->delete();

        return response()->json(['message' => 'QR code deleted.']);
    }
}
