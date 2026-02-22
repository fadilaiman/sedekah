<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'icon_url',
        'active',
    ];

    // Auto-clear cache when payment method is saved or deleted
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('payment_methods_active');
        });

        static::deleted(function () {
            Cache::forget('payment_methods_active');
        });
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // Relationships

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Check if payment method is currently in use
    public function isInUse(): bool
    {
        return $this->qrCodes()->exists()
            || Submission::where('payment_method_id', $this->id)->exists();
    }
}
