<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'booking_type',
        'street_address',
        'apartment_floor',
        'city',
        'state',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'cover_image'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'activity_category');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'activity_amenity');
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(ActivityPricing::class);
    }

    public function additionalServices(): HasMany
    {
        return $this->hasMany(ActivityAdditionalService::class);
    }

    public function workingHours(): HasMany
    {
        return $this->hasMany(ActivityWorkingHour::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ActivityImage::class);
    }
}
