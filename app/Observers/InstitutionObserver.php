<?php

namespace App\Observers;

use App\Jobs\PostToDaun;
use App\Models\Institution;
use App\Models\Submission;

class InstitutionObserver
{
    /**
     * Handle the Institution "updated" event.
     * Fires when institution is verified (verified_at changes from null to timestamp).
     */
    public function updated(Institution $institution): void
    {
        // Check if verified_at changed from null to a value
        if (
            $institution->wasChanged('verified_at')
            && $institution->verified_at !== null
            && $institution->getOriginal('verified_at') === null
        ) {
            // Try to find the submitter from the matching submission
            $submitterName = Submission::where('institution_name', $institution->name)
                ->where('status', 'approved')
                ->latest('reviewed_at')
                ->value('submitter_name');

            PostToDaun::dispatch('institution_verified', [
                'institution_name' => $institution->name,
                'state' => $institution->state,
                'slug' => $institution->slug,
                'submitter_name' => $submitterName,
            ]);
        }
    }
}
