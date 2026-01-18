<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Content extends Model
{
    protected $fillable = [
        'user_id',
        'activity_id',
        'title',
        'description',
        'reel_media',
        'cover_photo',
        'type',
        'is_published',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'content_categories');
    }
}
