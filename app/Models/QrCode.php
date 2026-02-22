<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QrCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'payment_method_id',
        'qr_image_path',
        'qr_image_url',
        'qr_content',
        'status',
        'expected_amount',
    ];

    protected function casts(): array
    {
        return [
            'expected_amount' => 'decimal:2',
        ];
    }

    // Relationships

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
