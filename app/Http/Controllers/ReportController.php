<?php

namespace App\Http\Controllers;

class ReportController extends Controller
{
    public function __invoke()
    {
        $livewireComponent = 'reports.index';
        $pageSection = 'full-content-no-padding';

        // if (auth()->user()->isManager()) {
        //     $pageSection = null;
        //     $title = trans('reports.title_manager');
        //     $livewireComponent = 'reports.manager';
        // }

        return view('pages.livewire', [
            'livewire' => $livewireComponent,
            'pageSection' => $pageSection,
        ]);
    }
}
