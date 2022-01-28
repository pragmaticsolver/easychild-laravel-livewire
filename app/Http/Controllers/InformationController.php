<?php

namespace App\Http\Controllers;

class InformationController extends Controller
{
    public function index()
    {
        $title = trans('informations.index_title');

        return view('pages.livewire', [
            'livewire' => 'informations.index',
            'title' => $title,
        ]);
    }

    public function create()
    {
        $title = trans('informations.create_title');

        return view('pages.livewire', [
            'livewire' => 'informations.create',
            'title' => $title,
        ]);
    }
}
