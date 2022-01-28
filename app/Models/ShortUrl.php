<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortUrl extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function linkFor($for, $routeLink = true)
    {
        $random = Str::random(8);

        while (self::where('from', $random)->count()) {
            $random = Str::random(8);
        }

        self::create([
            'from' => $random,
            'to' => $for,
        ]);

        if ($routeLink) {
            return route('shorturl', $random);
        }

        return $random;
    }
}
