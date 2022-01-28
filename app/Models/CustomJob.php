<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomJob extends Model
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'user_ids' => 'array',
        'auth_id' => 'integer',
        'due_at' => 'datetime',
    ];

    public function related()
    {
        return $this->morphTo();
    }
}
