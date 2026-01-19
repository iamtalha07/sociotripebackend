<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostActivity extends Model
{
    protected $guarded = [
        'id'
    ];

    public function targetCities()
    {
        return $this->hasMany(BoostingTargetCity::class, 'boost_activity_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
