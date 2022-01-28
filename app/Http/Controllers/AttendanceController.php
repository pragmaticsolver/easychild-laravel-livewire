<?php

namespace App\Http\Controllers;

class AttendanceController extends Controller
{
    public function __invoke()
    {
        $title = auth()->user()->organization->name;
        $navLinks = [];

        $navLinks[] = [
            'text' => trans('organizations.profile_title'),
            'href' => route('organizations.profile'),
            'active' => request()->routeIs('organizations.profile'),
        ];

        $navLinks[] = [
            'text' => trans('contracts.title'),
            'href' => route('contracts.index'),
            'active' => request()->routeIs('contracts.index'),
        ];

        $navLinks[] = [
            'text' => trans('attendances.title'),
            'href' => route('logs.attendances'),
            'active' => request()->routeIs('logs.attendances'),
        ];

        return view('pages.livewire', [
            'livewire' => 'logs.attendance',
            'title' => $title,
            'navLinks' => $navLinks,
        ]);
    }
}
