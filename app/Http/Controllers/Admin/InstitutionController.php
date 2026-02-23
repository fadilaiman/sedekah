<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionRequest;
use App\Models\Institution;
use App\Models\PaymentMethod;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institution::withCount(['qrCodes' => fn ($q) => $q->where('status', 'active')])
            ->with(['activeQrCodes.paymentMethod']);

        if ($search = $request->get('search')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
            );
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($state = $request->get('state')) {
            $query->where('state', $state);
        }

        // Filter by verification status
        $verified = $request->get('verified', 'unverified'); // Default to unverified
        if ($verified === 'verified') {
            $query->whereNotNull('verified_at');
        } elseif ($verified === 'unverified') {
            $query->whereNull('verified_at');
        }
        // If 'all', show all institutions (no filter)

        $institutions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20))
            ->withQueryString();

        return Inertia::render('Admin/Institutions/Index', [
            'institutions' => $institutions,
            'filters' => $request->only(['search', 'category', 'state', 'verified', 'per_page']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Institutions/Create', [
            'paymentMethods' => PaymentMethod::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(InstitutionRequest $request)
    {
        $institution = Institution::create($request->validated());
        Cache::forget('homepage_featured');
        return redirect()->route('admin.institutions.edit', $institution->id)
            ->with('success', 'Institusi berjaya dicipta.');
    }

    public function edit(Institution $institution)
    {
        return Inertia::render('Admin/Institutions/Edit', [
            'institution' => $institution->load(['qrCodes.paymentMethod']),
            'paymentMethods' => PaymentMethod::where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(InstitutionRequest $request, Institution $institution)
    {
        $institution->update($request->validated());
        $this->bustInstitutionCache($institution);
        return redirect()->back()->with('success', 'Institusi berjaya dikemaskini.');
    }

    public function destroy(Institution $institution)
    {
        $this->bustInstitutionCache($institution);
        $institution->delete();
        return redirect()->route('admin.institutions.index')
            ->with('success', 'Institusi berjaya dipadam.');
    }

    // QR code management
    public function storeQr(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'qr_image' => 'required|image|mimes:jpeg,png|max:1024',
        ]);

        $file = $request->file('qr_image');
        if (!$file) {
            return redirect()->back()->withErrors(['qr_image' => 'File upload failed']);
        }

        try {
            $filename = uniqid('qr_') . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('qr-codes', $filename, 'public');

            QrCode::updateOrCreate(
                ['institution_id' => $institution->id, 'payment_method_id' => $validated['payment_method_id']],
                ['qr_image_path' => $path, 'qr_image_url' => Storage::url($path), 'status' => 'active']
            );

            $this->bustInstitutionCache($institution);
            return redirect()->back()->with('success', 'QR code berjaya dimuat naik.');
        } catch (\Exception $e) {
            \Log::error('QR code upload failed', ['error' => $e->getMessage()]);
            return redirect()->back()->withErrors(['qr_image' => 'Gagal menyimpan fail: ' . $e->getMessage()]);
        }
    }

    public function destroyQr(Institution $institution, QrCode $qrCode)
    {
        if ($qrCode->institution_id !== $institution->id) {
            abort(403);
        }
        if ($qrCode->qr_image_path) {
            Storage::disk('public')->delete($qrCode->qr_image_path);
        }
        $qrCode->delete();
        $this->bustInstitutionCache($institution);
        return redirect()->back()->with('success', 'QR code berjaya dipadam.');
    }

    public function toggleQrStatus(Institution $institution, QrCode $qrCode)
    {
        if ($qrCode->institution_id !== $institution->id) {
            abort(403);
        }
        $qrCode->update(['status' => $qrCode->status === 'active' ? 'inactive' : 'active']);
        $this->bustInstitutionCache($institution);
        return redirect()->back()->with('success', 'Status QR code berjaya dikemaskini.');
    }

    private function bustInstitutionCache(Institution $institution): void
    {
        Cache::forget("institution_{$institution->id}");
        Cache::forget("institution_slug_{$institution->slug}");
        Cache::forget('homepage_featured');
    }
}
