<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\QrCode;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Submission::with('reviewer')
            ->orderBy('created_at', 'desc');

        if ($status = $request->get('status', 'pending')) {
            $query->where('status', $status);
        }

        $submissions = $query->paginate(15)->withQueryString();

        // Attach duplicate detection info
        $submissions->getCollection()->each(function ($submission) {
            // Exact match (same name + state + city) first, then partial name matches
            $exactMatches = Institution::where('name', $submission->institution_name)
                ->where('state', $submission->institution_state)
                ->where('city', $submission->institution_city)
                ->get(['id', 'name', 'city', 'state', 'slug']);

            $partialMatches = Institution::where('name', 'like', "%{$submission->institution_name}%")
                ->when($exactMatches->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $exactMatches->pluck('id')))
                ->limit(3)
                ->get(['id', 'name', 'city', 'state', 'slug']);

            $submission->duplicates = $exactMatches->merge($partialMatches);
            $submission->exact_duplicate = $exactMatches->isNotEmpty();

            // Also check other pending submissions with same name
            $submission->pending_duplicates = Submission::where('id', '!=', $submission->id)
                ->where('status', 'pending')
                ->where('institution_name', $submission->institution_name)
                ->limit(3)
                ->get(['id', 'institution_name as name', 'institution_city as city', 'institution_state as state']);
        });

        return Inertia::render('Admin/Submissions/Index', [
            'submissions' => $submissions,
            'filters' => ['status' => $request->get('status', 'pending')],
        ]);
    }

    public function show(Submission $submission)
    {
        $exactMatches = Institution::where('name', $submission->institution_name)
            ->where('state', $submission->institution_state)
            ->where('city', $submission->institution_city)
            ->get(['id', 'name', 'city', 'state', 'slug']);

        $partialMatches = Institution::where('name', 'like', "%{$submission->institution_name}%")
            ->when($exactMatches->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $exactMatches->pluck('id')))
            ->limit(5)
            ->get(['id', 'name', 'city', 'state', 'slug']);

        $duplicates = $exactMatches->merge($partialMatches);

        $pendingDuplicates = Submission::where('id', '!=', $submission->id)
            ->where('status', 'pending')
            ->where('institution_name', $submission->institution_name)
            ->limit(3)
            ->get(['id', 'institution_name as name', 'institution_city as city', 'institution_state as state']);

        return Inertia::render('Admin/Submissions/Show', [
            'submission' => $submission,
            'duplicates' => $duplicates,
            'exact_duplicate' => $exactMatches->isNotEmpty(),
            'pending_duplicates' => $pendingDuplicates,
        ]);
    }

    public function approve(Submission $submission)
    {
        if ($submission->status !== 'pending') {
            return redirect()->back()->with('error', 'Penyerahan ini bukan dalam status menunggu.');
        }

        $institution = Institution::create([
            'name'       => $submission->institution_name,
            'category'   => $submission->institution_category,
            'state'      => $submission->institution_state,
            'city'       => $submission->institution_city,
            'address'    => $submission->institution_address,
            'maps_url'   => $submission->institution_maps_url,
            'is_verified' => false,
        ]);

        // Attach QR code if present
        if ($submission->qr_image_url && $submission->payment_method_id) {
            QrCode::create([
                'institution_id'    => $institution->id,
                'payment_method_id' => $submission->payment_method_id,
                'qr_image_url'      => $submission->qr_image_url,
                'status'            => 'active',
            ]);
        }

        $submission->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        Cache::forget('homepage_featured');
        Cache::forget('homepage_stats');
        Cache::forget('dashboard_stats');

        return redirect()->back()->with('success', "Penyerahan diluluskan. Institusi '{$institution->name}' telah dicipta.");
    }

    public function reject(Request $request, Submission $submission)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        if ($submission->status !== 'pending') {
            return redirect()->back()->with('error', 'Penyerahan ini bukan dalam status menunggu.');
        }

        $submission->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
            'reviewed_by'      => auth()->id(),
            'reviewed_at'      => now(),
        ]);

        return redirect()->back()->with('success', 'Penyerahan telah ditolak.');
    }
}
