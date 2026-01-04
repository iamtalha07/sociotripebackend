<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAdditionalService extends Model
{
    protected $fillable = [
        'activity_id',
        'service_name',
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
