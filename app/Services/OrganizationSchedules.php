<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class OrganizationSchedules
{
    public $schedules;
    public $organization;
    public $group;
    public $date;
    public $addUser;
    public $filters;

    public function __construct($addUser = false, $filters = [])
    {
        $this->addUser = $addUser;
        $this->filters = $filters;

        $this->date = now()->format('Y-m-d');

        return $this;
    }

    public function setOrganization($organization)
    {
        $this->organization = $organization;

        return $this;
    }

    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function applyNormalFilters($query)
    {
        $colFilters = collect($this->filters);
        $normalFilters = $colFilters->where('normal', true)->all();

        if (count($normalFilters) && auth()->user()->isManagerOrPrincipal()) {
            foreach ($normalFilters as $filter) {
                $query->having($filter['col'], $filter['operator'], $filter['query']);
            }
        }
    }

    public function applyExtraFilters($data)
    {
        $returnVal = [];

        $colFilters = collect($this->filters);
        $extraFilters = $colFilters->where('normal', false)->all();

        if (count($extraFilters)) {
            foreach ($extraFilters as $filter) {
                if (Arr::has($data, $filter['col']) && collect($data)->pull($filter['col']) == $filter['query']) {
                    $returnVal[] = true;
                } else {
                    $returnVal[] = false;
                }
            }

            return in_array(false, $returnVal) ? false : true;
        }

        return true;
    }

    public function fetch($search = null, $sortBy = null, $sortOrder = null)
    {
        if (! $search) {
            $search = '';
        }

        $term = '%' . $search . '%';

        $userQuery = User::query()
            ->where('role', 'User')
            ->select('*', DB::raw('CONCAT(given_names, " ", last_name) as user_name'), DB::raw('uuid as user_uuid'))
            ->addSelect([
                'group_name' => DB::table('group_user')
                    ->whereColumn('group_user.user_id', 'users.id')
                    ->join('groups', 'groups.id', 'group_user.group_id')
                    ->select('groups.name')
                    ->limit(1),
            ]);

        if ($sortBy && $sortOrder) {
            $userQuery->orderBy($sortBy, $sortOrder);
        } else {
            $userQuery->orderBy('group_name');
        }

        if (count($this->filters)) {
            $this->applyNormalFilters($userQuery);
        }

        if ($this->group) {
            if ($search) {
                $userQuery->where(function ($query) use ($term) {
                    $query->where('given_names', 'LIKE', $term)
                        ->orWhere('last_name', 'LIKE', $term);
                });
            }

            $userQuery->whereHas('groups', function ($q) {
                $q->where('groups.id', $this->group->id);
            });
        } else {
            if ($search) {
                $userQuery->whereIn('users.id', function ($query) use ($term) {
                    $query->select('group_user.user_id')
                        ->from('group_user')
                        ->join('groups', 'groups.id', 'group_user.group_id')
                        ->join('users as u1', 'u1.id', 'group_user.user_id')
                        ->where(function ($q) use ($term) {
                            $q->orWhere('groups.name', 'LIKE', $term)
                                ->orWhere('u1.given_names', 'LIKE', $term)
                                ->orWhere('u1.last_name', 'LIKE', $term);
                        });
                });
            }
        }

        $orgSettings = [];
        if ($this->organization) {
            $userQuery->where('organization_id', $this->organization->id);

            $orgSettings = $this->organization->settings;
        }

        $users = $userQuery->get();

        $submittedSchedules = Schedule::query()
            ->where('date', $this->date)
            ->whereIn('user_id', $users->pluck('id')->toArray())
            ->get();

        $userWithUpdatedSchedule = $submittedSchedules->pluck('user_id')->toArray();

        $schedules = [];
        $mealOptions = ['breakfast', 'lunch', 'dinner'];
        foreach ($users as $user) {
            $userSettings = $user->settings;
            if (! $userSettings) {
                $userSettings = [];
            }

            $eatsOnsite = $orgSettings['eats_onsite'];
            if (Arr::has($userSettings, 'eats_onsite')) {
                foreach ($mealOptions as $option) {
                    if (Arr::has($userSettings['eats_onsite'], $option) && gettype($userSettings['eats_onsite'][$option]) == 'boolean') {
                        $eatsOnsite[$option] = $userSettings['eats_onsite'][$option];
                    } else {
                        $eatsOnsite[$option] = $orgSettings['eats_onsite'][$option];
                    }

                    if (! $orgSettings['eats_onsite'][$option]) {
                        $eatsOnsite[$option] = null;
                    }
                }
            }

            $allergy = null;
            if (Arr::has($userSettings, 'allergies')) {
                $allergy = $userSettings['allergies'];
            }

            $userAvailable = $orgSettings['availability'] == 'available';
            if (Arr::has($userSettings, 'availability') && $userSettings['availability']) {
                $userAvailable = $userSettings['availability'] == 'available';
            }

            $data = [
                'given_names' => $user->given_names,
                'last_name' => $user->last_name,
                'user_name' => $user->full_name,
                'group_name' => $user->group_name,
                'allergy' => $allergy,
                'eats_onsite' => $eatsOnsite,
                'available' => $userAvailable,
                'presence_start' => null,
                'presence_end' => null,
                'status' => 'approved',
                'date' => $this->date,
                'user_id' => $user->id,
                'user_uuid' => $user->uuid,
                'uuid' => null,
                'start' => null,
                'end' => null,
            ];

            if ($this->addUser) {
                $data['user'] = $user;
            }

            if (in_array($user->id, $userWithUpdatedSchedule)) {
                $updatedSchedule = $submittedSchedules
                    ->where('user_id', $user->id)
                    ->first();

                $data['uuid'] = $updatedSchedule->uuid;
                $data['available'] = $updatedSchedule->available;
                $data['status'] = $updatedSchedule->status;
                $data['start'] = $updatedSchedule->start;
                $data['end'] = $updatedSchedule->end;
                $data['presence_start'] = $updatedSchedule->presence_start;
                $data['presence_end'] = $updatedSchedule->presence_end;
                $data['eats_onsite'] = $updatedSchedule->eats_onsite;
            }

            if (count($this->filters)) {
                if ($this->applyExtraFilters($data)) {
                    $schedules[] = $data;
                }
            } else {
                $schedules[] = $data;
            }
        }

        return collect($schedules);
    }
}
