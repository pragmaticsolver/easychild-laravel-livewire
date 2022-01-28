<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentLink extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'child_id' => 'integer',
        'linked' => 'boolean',
    ];

    public function child()
    {
        return $this->hasOne(User::class, 'id', 'child_id');
    }
}
