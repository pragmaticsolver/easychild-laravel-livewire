<?php

namespace App\Models;

use App\Traits\HasSearchScope;
use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Contract extends Model
{
    use HasFactory, HasSearchScope, Uuidable;

    protected $guarded = [];

    protected $casts = [
        'time_per_day' => 'float',
        'overtime' => 'float',
        'organization_id' => 'integer',
        'legal' => 'boolean',
        'can_collect' => 'boolean',
        'emergency_contact' => 'boolean',
    ];

    protected $searchConfig = [
        'table' => 'contracts',
        'cols' => [
            'contracts.title',
            // [
            //     'select' => 'users.id',
            //     'from' => 'users',
            //     'join' => ['organizations', 'users.organization_id', '=', 'organizations.id'],
            //     'where' => [
            //         'organizations.name',
            //         'organizations.address',
            //     ],
            // ],
        ],
    ];

    public function getBringUntilFormattedAttribute()
    {
        if ($this->bring_until) {
            return Str::replaceLast(':00', '', $this->bring_until);
        }

        return null;
    }

    public function getCollectUntilFormattedAttribute()
    {
        if ($this->collect_until) {
            return Str::replaceLast(':00', '', $this->collect_until);
        }

        return null;
    }
}
