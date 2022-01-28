<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'legal' => 'boolean',
        'emergency_contact' => 'boolean',
        'can_collect' => 'boolean',
        'user_id' => 'integer',
    ];

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ?
            Storage::disk('avatars')->url($this->avatar) :
            'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=000000&background=E5E7EB';
    }
}
