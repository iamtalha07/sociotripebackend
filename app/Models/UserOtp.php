<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model
{
    protected $fillable = [
        'code',
        'user_id',
        'is_expired',
        'type'
    ];
}
