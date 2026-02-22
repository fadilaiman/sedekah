<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $table = 'institution_categories';

    protected $fillable = [
        'value',
        'label',
        'icon',
        'color',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // Auto-clear cache when category is saved or deleted
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('inertia_categories');
            Cache::forget('category_values');
        });

        static::deleted(function () {
            Cache::forget('inertia_categories');
            Cache::forget('category_values');
        });
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('label');
    }

    // Check if category is currently in use by any institution
    public function isInUse(): bool
    {
        return Institution::where('category', $this->value)->exists();
    }
}
