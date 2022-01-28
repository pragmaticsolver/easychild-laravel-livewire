<?php

namespace App\Traits;

use App\Models\Schedule;

trait HasPrincipalDashboard
{
    public function getManagerDashboardDetail()
    {
        $org = auth()->user()->organization;

        return $this->getDashboardDetail($org);
    }

    public function getPrincipalDashboardDetail()
    {
        $principalGroup = $this->principal_current_group;

        return $this->getDashboardDetail($principalGroup);
    }

    public function getDashboardDetail($orgOrGroup)
    {
        $usersInGroup = $orgOrGroup->users()
            ->where('role', 'User')
            ->pluck('users.id')
            ->toArray();

        $userSchedules = Schedule::query()
            ->whereIn('user_id', $usersInGroup)
            // ->whereIn('user_id', function ($query) use ($orgOrGroup) {
            //     $query->select('id')
            //         ->from('users')
            //         ->where('role', 'User');

            //     if (class_basename($orgOrGroup) == 'Group') {
            //         $query->join('group_user', 'group_user.user_id', 'users.id')
            //             ->where('group_user.group_id', $orgOrGroup);
            //     } else {
            //         $query->where('organization_id', $orgOrGroup->id);
            //     }
            // })
            ->where('date', now()->format('Y-m-d'))
            ->where('available', true)
            ->get();

        $prescenseCurrent = $userSchedules
            ->whereNotNull('presence_start')
            ->whereNull('presence_end')
            ->all();

        $timeSchedules = [
            'currentValue' => $userSchedules->count(),
            'total' => count($usersInGroup),
        ];

        $prescenseSchedule = [
            'currentValue' => count($prescenseCurrent),
            'total' => count($userSchedules),
        ];

        $mealPlans = [];
        foreach (['breakfast', 'lunch', 'dinner'] as $mealType) {
            $eating = collect($userSchedules->toArray())
                ->where("eats_onsite.{$mealType}", true)
                ->all();

            $notEating = collect($userSchedules->toArray())
                ->where("eats_onsite.{$mealType}", false)
                ->all();

            $mealPlan = [
                'currentValue' => count($notEating),
                'total' => count($eating),
            ];

            $mealPlans[$mealType] = $this->calculateTotalPercent($mealPlan);
        }

        return [$this->calculatePercent($timeSchedules), $this->calculatePercent($prescenseSchedule), $mealPlans];
    }

    private function calculateTotalPercent($data)
    {
        $newData = [
            'currentValue' => $data['total'],
            'total' => $data['total'] + $data['currentValue'],
        ];

        $newCalculatedData = $this->calculatePercent($newData);

        $newCalculatedData['currentValue'] = $data['currentValue'];
        $newCalculatedData['total'] = $data['total'];

        return $newCalculatedData;
    }

    private function calculatePercent($data)
    {
        $currentValue = $data['currentValue'];
        $total = $data['total'];

        $percent = 0;
        if ($total != 0) {
            $percent = ($currentValue / $total) * 100;
            $percent = 100 - $percent;
        }

        if ($percent == 100) {
            $percent = 101;
        }

        $data['percent'] = '100 '.number_format($percent);

        return $data;
    }
}
