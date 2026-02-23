<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Institution extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'state',
        'city',
        'address',
        'description',
        'website_url',
        'contact_email',
        'contact_phone',
        'external_campaign_url',
        'url',
        'lat',
        'lng',
        'maps_url',
        'coords_source',
        'logo_image_id',
        'banner_image_id',
        'is_verified',
        'verified_at',
        'scraped_at',
    ];

    protected function casts(): array
    {
        return [
            'lat' => 'decimal:8',
            'lng' => 'decimal:8',
            'verified_at' => 'datetime',
            'scraped_at' => 'datetime',
        ];
    }

    // Auto-generate slug from name before creating
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Institution $institution) {
            if (empty($institution->slug)) {
                $institution->slug = static::generateUniqueSlug($institution->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    // Media library collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('banner')->singleFile();
    }

    // Relationships

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function activeQrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class)->where('status', 'active');
    }

    public function featuredInstitution(): HasOne
    {
        return $this->hasOne(FeaturedInstitution::class);
    }

    // Accessor: is_verified → verified_at
    protected function isVerified(): Attribute
    {
        return Attribute::make(
            get: fn () => !is_null($this->verified_at),
            set: fn (bool $value) => ['verified_at' => $value ? now() : null],
        );
    }

    // Append is_verified to serialization
    protected $appends = ['is_verified'];

    // Scopes

    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByState($query, string $state)
    {
        return $query->where('state', $state);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('city', 'like', "%{$term}%");
        });
    }
}
