<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SubNavigationHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $title = trans('users.title');
        $user = auth()->user();

        if ($user->isManager()) {
            $title = trans('users.principal_title', ['org' => $user->organization->name]);
        }

        if ($user->isPrincipal()) {
            $principalGroup = $user->principal_current_group;

            $title = trans('users.principal_title', ['org' => $principalGroup->name]);
        }

        $search = $this->checkQuery($request, 'search');
        $role = $this->checkQuery($request, 'role');

        return view('pages.livewire', [
            'livewire' => 'users.index',
            'title' => $title,
            'data' => compact('search', 'role'),
        ]);
    }

    public function create()
    {
        $title = trans('users.add_new_title');
        $back = 'users.index';

        return view('pages.livewire', [
            'livewire' => 'users.create',
            'title' => $title,
            'back' => $back,
        ]);
    }

    public function edit(User $user, $type = 'base')
    {
        $title = $user->full_name;
        $back = 'users.index';

        $extraRoutes = [
            route('users.create'),
            route('offline'),
        ];

        if (url()->previous() && ! in_array(url()->previous(), $extraRoutes)) {
            $back = url()->previous();
        }

        [$navLinks, $type] = SubNavigationHelper::userEdit($user, $type);

        $isLinkAbsolute = false;

        if (Str::startsWith($back, 'http')) {
            $isLinkAbsolute = true;
        }

        return view('pages.livewire', [
            'livewire' => "users.edit.{$type}",
            'title' => $title,
            'data' => compact('user'),
            'back' => $back,
            'isLinkAbsolute' => $isLinkAbsolute,
            'navLinks' => $navLinks,
        ]);
    }

    public function profile()
    {
        $user = auth()->user();
        $livewire = 'users.profile.profile';

        [$navLinks, $title, $type] = SubNavigationHelper::userProfile($user, request()->input('type'));

        if ($type == 'children') {
            $livewire = 'users.profile.children-profile';
        }

        return view('pages.livewire', [
            'livewire' => $livewire,
            'title' => $title,
            'navLinks' => $navLinks,
        ]);
    }
}
