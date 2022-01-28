<?php

namespace App\Http\Controllers;

class PresenceController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        return view('pages.dashboard.presence', compact('user'));
    }
}
