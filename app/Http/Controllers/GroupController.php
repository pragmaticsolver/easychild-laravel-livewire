<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\SubNavigationHelper;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $title = auth()->user()->organization->name;
        $search = $this->checkQuery($request, 'search');

        $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'groups.index',
            'title' => $title,
            'navLinks' => $navLinks,
            'data' => compact('search'),
        ]);
    }

    public function create()
    {
        $title = auth()->user()->organization->name;
        $back = 'groups.index';

        $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'groups.create',
            'title' => $title,
            'data' => [
                'mainTitle' => trans('groups.add_new_title'),
            ],
            'navLinks' => $navLinks,
            'back' => $back,
        ]);
    }

    public function edit(Group $group)
    {
        $title = auth()->user()->organization->name;
        $back = 'groups.index';

        $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'groups.edit',
            'title' => $title,
            'navLinks' => $navLinks,
            'data' => compact('group'),
            'back' => $back,
        ]);
    }
}
