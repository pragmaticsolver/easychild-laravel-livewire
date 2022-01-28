<?php

namespace App\Models;

use App\Traits\HasModelLimit;
use App\Traits\HasSearchScope;
use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasSearchScope, Uuidable, HasModelLimit, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'organization_id' => 'integer',
    ];

    protected $searchConfig = [
        'table' => 'groups',
        'cols' => [
            'groups.name',
            [
                'select' => 'g1.id',
                'from' => 'groups as g1',
                'join' => ['organizations', 'g1.organization_id', '=', 'organizations.id'],
                'where' => [
                    'organizations.address',
                    'organizations.name',
                ],
            ],
        ],
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
