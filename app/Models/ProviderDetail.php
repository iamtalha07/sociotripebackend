<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProviderDetail extends Model
{
    protected $fillable = [
        'user_id',
        'selfie_verification_image',
        'id_front_verification_image',
        'id_back_verification_image',
    ];
}
