<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\SubNavigationHelper;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $title = trans('organizations.title');

        $search = $this->checkQuery($request, 'search');

        return view('pages.livewire', [
            'livewire' => 'organizations.index',
            'title' => $title,
            'data' => compact('search'),
        ]);
    }

    public function create()
    {
        $title = trans('organizations.add_new_title');
        $back = 'organizations.index';

        return view('pages.livewire', [
            'livewire' => 'organizations.create',
            'title' => $title,
            'back' => $back,
        ]);
    }

    public function edit(Organization $organization)
    {
        $title = $organization->name;
        $back = 'organizations.index';

        // $navLinks = SubNavigationHelper::adminOrgView($organization);

        return view('pages.livewire', [
            'livewire' => 'organizations.edit',
            'title' => $title,
            'data' => compact('organization'),
            'back' => $back,
            // 'navLinks' => $navLinks,
        ]);
    }

    public function profile()
    {
        $title = auth()->user()->organization->name;

        $navLinks = SubNavigationHelper::orgProfile();

        return view('pages.livewire', [
            'livewire' => 'organizations.profile',
            'title' => $title,
            'navLinks' => $navLinks,
        ]);
    }
}
