<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedInstitution extends Model
{
    protected $fillable = [
        'institution_id',
        'order',
    ];

    // Relationships

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Scopes

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
