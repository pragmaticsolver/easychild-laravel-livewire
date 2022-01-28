<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class TranslationUpdateController extends Controller
{
    public function __invoke()
    {
        Artisan::call('translation_sheet:pull');
        Artisan::call('optimize:clear');

        session()->flash('success', trans('extras.translation_updated'));

        return redirect(route('dashboard'));
    }
}
