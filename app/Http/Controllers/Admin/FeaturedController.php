<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedInstitution;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FeaturedController extends Controller
{
    public function edit()
    {
        $featured = FeaturedInstitution::with('institution:id,name,category,city,state,slug')
            ->ordered()
            ->get();

        // Only show verified institutions in the picker
        $institutions = Institution::verified()
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'city', 'state', 'slug']);

        return Inertia::render('Admin/Featured/Edit', [
            'featured'     => $featured,
            'institutions' => $institutions,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'institution_ids'   => 'nullable|array|max:8',
            'institution_ids.*' => 'exists:institutions,id',
        ]);

        $institutionIds = $request->institution_ids ?? [];

        // Ensure all institutions are verified (skip if empty list)
        if (!empty($institutionIds)) {
            $verifiedCount = Institution::whereIn('id', $institutionIds)->verified()->count();

            if ($verifiedCount !== count($institutionIds)) {
                return back()->withErrors(['institution_ids' => 'Semua institusi mesti telah disahkan.']);
            }
        }

        FeaturedInstitution::truncate();

        foreach ($institutionIds as $order => $id) {
            FeaturedInstitution::create([
                'institution_id' => $id,
                'order'          => $order + 1,
            ]);
        }

        // Clear cache so homepage displays updated featured institutions
        Cache::forget('homepage_featured');

        return redirect()->back()->with('success', 'Institusi pilihan berjaya disimpan.');
    }
}
