<?php

namespace App\Http\Controllers;

class MealPlanController extends Controller
{
    public function __invoke()
    {
        $title = auth()->user()->organization->name;

        return view('pages.livewire', [
            'livewire' => 'vendor.dashboard',
            'title' => $title,
        ]);
    }
}
