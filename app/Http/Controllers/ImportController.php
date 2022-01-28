<?php

namespace App\Http\Controllers;

class ImportController extends Controller
{
    public function children()
    {
        $title = trans('import.title');

        return view('pages.livewire', [
            'livewire' => 'import.children',
            'title' => $title,
        ]);
    }
}
