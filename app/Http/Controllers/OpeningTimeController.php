<?php

namespace App\Http\Controllers;

use App\Services\SubNavigationHelper;

class OpeningTimeController extends Controller
{
    public function __invoke()
    {
        $title = auth()->user()->organization->name;
        $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'opening-times.index',
            'title' => $title,
            'navLinks' => $navLinks,
        ]);
    }
}
