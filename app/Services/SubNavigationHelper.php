<?php

namespace App\Services;

use App\Models\User;

class SubNavigationHelper
{
    public static function userEdit($user, $type)
    {
        $navLinks = [];

        if ($user->role == 'User') {
            $acceptableEditTypes = ['base', 'parents', 'contacts', 'notes', 'documentation', 'log'];

            if (! in_array($type, $acceptableEditTypes)) {
                $type = $acceptableEditTypes[0];
                request()->request->remove('type');
                request()->request->add(['type' => $type]);
            }

            foreach ($acceptableEditTypes as $typeItem) {
                $navLinks[] = [
                    'text' => trans('users.sub-nav.'.$typeItem),
                    'href' => route('users.edit', ['user' => $user, 'type' => $typeItem]),
                    'active' => $type == $typeItem,
                ];
            }
        } else {
            $type = 'base';
        }

        return [$navLinks, $type];
    }

    public static function adminOrgView($organization)
    {
        $navLinks = [];

        $navLinks[] = [
            'text' => trans('organizations.edit'),
            'href' => route('organizations.edit', ['organization' => $organization]),
            'active' => request()->routeIs('organizations.edit'),
        ];

        $navLinks[] = [
            'text' => 'Import Children',
            'href' => route('import.children', ['organization' => $organization]),
            'active' => request()->routeIs('import.children'),
        ];

        return $navLinks;
    }

    public static function orgProfile()
    {
        $navLinks = [];

        $navLinks[] = [
            'text' => trans('nav.groups'),
            'href' => route('groups.index'),
            'active' => request()->routeIs('groups.*'),
        ];

        $navLinks[] = [
            'text' => trans('contracts.title'),
            'href' => route('contracts.index'),
            'active' => request()->routeIs('contracts.index'),
        ];

        $navLinks[] = [
            'text' => trans('dashboard.opening_times_title'),
            'href' => route('openingtimes.index'),
            'active' => request()->routeIs('openingtimes.index'),
        ];

        $navLinks[] = [
            'text' => trans('organizations.profile_title'),
            'href' => route('organizations.profile'),
            'active' => request()->routeIs('organizations.profile'),
        ];

        return $navLinks;
    }

    public static function userProfile(User $user, $type = null)
    {
        $navLinks = [];
        $title = trans('users.sub-nav.profile');

        if ($user->role == 'Parent') {
            $acceptableEditTypes = ['profile', 'children'];

            if (! in_array($type, $acceptableEditTypes)) {
                $type = $acceptableEditTypes[0];
            }

            if ($type == 'children') {
                $title = trans('users.sub-nav.children');
            }

            $hrefProfile = route('users.profile', ['type' => 'profile']);
            $hrefChildren = route('users.profile', ['type' => 'children']);

            $navLinks[] = [
                'text' => trans('users.sub-nav.profile'),
                'href' => $hrefProfile,
                'active' => $type == 'profile',
            ];

            $navLinks[] = [
                'text' => trans('users.sub-nav.children'),
                'href' => $hrefChildren,
                'active' => $type == 'children',
            ];
        }

        return [$navLinks, $title, $type];
    }
}
