<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPricing extends Model
{
    protected $fillable = [
        'activity_id',
        'category_name',
        'age_min',
        'age_max',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
