<?php

namespace App\Models;

use App\Traits\HasModelLimit;
use App\Traits\HasSearchScope;
use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Organization extends Model
{
    use HasSearchScope, Uuidable, HasModelLimit, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * User search column fields
     *
     * @var array
     */
    protected $searchConfig = [
        'table' => 'organizations',
        'cols' => [
            'organizations.name',
            'organizations.city',
            'organizations.zip_code',
            'organizations.street',
        ],
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? Storage::disk('avatars')->url($this->avatar) : asset('img/easychild.svg');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
