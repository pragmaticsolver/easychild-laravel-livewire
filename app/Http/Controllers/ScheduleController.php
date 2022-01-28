<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Organization;

class ScheduleController extends Controller
{
    public function index($type = null, $uuid = null)
    {
        $title = trans('schedules.create_title');

        $model = null;

        if (auth()->user()->isParent()) {
            return $this->create();
        } else {
            if (auth()->user()->isPrincipal()) {
                return $this->approve();
            }

            if ($type && $uuid) {
                if ($type === 'organization') {
                    $org = Organization::whereUuid($uuid)->first();

                    if ($org) {
                        $title = trans('schedules.title', [
                            'name' => $org->name,
                            'type' => trans('schedules.type_org'),
                        ]);
                        $model = $org;
                    }
                }

                if ($type === 'group') {
                    $group = Group::whereUuid($uuid)->first();

                    if ($group) {
                        $title = trans('schedules.title', [
                            'name' => $group->name,
                            'type' => trans('schedules.type_group'),
                        ]);
                        $model = $group;
                    }
                }
            } else {
                return $this->approve();
            }

            return view('pages.livewire', [
                'livewire' => 'schedules.index',
                'title' => $title,
                'data' => [
                    'model' => $model,
                ],
            ]);
        }

        session()->flash('error', trans('extras.admin_and_manager_only'));

        return redirect(route('dashboard'));
    }

    public function create()
    {
        $title = trans('schedules.create_title');

        return view('pages.livewire', [
            'livewire' => 'schedules.create',
            'title' => $title,
            'showChildSwitcher' => auth()->user()->isParent(),
        ]);
    }

    public function approve()
    {
        $title = trans('schedules.approve_title');

        return view('pages.livewire', [
            'livewire' => 'schedules.approve',
            'title' => $title,
            'data' => request()->only('date'),
        ]);
    }
}
