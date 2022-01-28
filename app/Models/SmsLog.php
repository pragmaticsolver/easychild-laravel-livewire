<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $casts = [
        'data' => 'array',
    ];

    protected $guarded = [];
}
