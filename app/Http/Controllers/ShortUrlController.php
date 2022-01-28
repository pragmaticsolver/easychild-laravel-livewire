<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;

class ShortUrlController extends Controller
{
    public function index(ShortUrl $shortUrl)
    {
        $shortUrl->update([
            'last_used' => now(),
        ]);

        return redirect($shortUrl->to);
    }
}
