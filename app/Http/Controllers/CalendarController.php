<?php

namespace App\Http\Controllers;

class CalendarController extends Controller
{
    public function __invoke()
    {
        $title = 'Calendar';

        // $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'calendar.index',
            'pageSection' => 'full-content-no-padding',
            // 'title' => $title,
        ]);
    }
}
