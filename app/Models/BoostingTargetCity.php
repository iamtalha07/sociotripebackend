<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostingTargetCity extends Model
{
    protected $guarded = [
        'id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
