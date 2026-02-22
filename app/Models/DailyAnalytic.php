<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DailyAnalytic extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'page_visits',
        'qr_downloads',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    /**
     * Get today's analytics record.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Increment a field for today's analytics, creating record if needed.
     * Uses upsert pattern for atomic increment.
     */
    public static function incrementToday(string $field): void
    {
        $today = Carbon::today();

        static::upsert(
            [[
                'date' => $today,
                'page_visits' => $field === 'page_visits' ? 1 : 0,
                'qr_downloads' => $field === 'qr_downloads' ? 1 : 0,
            ]],
            ['date'],
            [$field => \DB::raw("{$field} + 1")]
        );
    }
}
