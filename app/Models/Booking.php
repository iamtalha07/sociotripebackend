<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'provider_id',
        'activity_id',
        'booking_date',
        'arrival_time',
        'price_ht',
        'vat',
        'total_price',
        'status',
        'booking_day'
    ];

    // Customer who made the booking
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Provider (current logged-in user)
    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    // Activity booked
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    // Booking details (pricing + additional services)
    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }
}
