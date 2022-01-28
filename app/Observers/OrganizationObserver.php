<?php

namespace App\Observers;

use App\Models\Conversation;
use App\Models\Organization;

class OrganizationObserver
{
    private function addOpeningTimesAndOtherSettings($org)
    {
        $openingTimes = [];

        for ($x = 0; $x < 5; $x++) {
            $openingTimes[$x] = [
                'key' => $x,
                'start' => config('setting.defaultOpeningTimes.start'),
                'end' => config('setting.defaultOpeningTimes.end'),
            ];
        }

        $org->settings = [
            'opening_times' => $openingTimes,
            'limitations' => [
                'lead_time' => config('setting.minLeadDays'),
                'selection_time' => config('setting.minSelectionDays'),
            ],
            'schedule_auto_approve' => false,
            'eats_onsite' => config('setting.organizationScheduleSettings.eats_onsite'),
            'availability' => config('setting.organizationScheduleSettings.availability'),
            'food_lock_time' => '08:00:00',
            'schedule_lock_time' => '08:00:00',
            'access' => [
                'messages' => true,
                'informations' => true,
            ],
        ];
        $org->save();
    }

    public function creating($obj)
    {
        $obj->address = $this->createAddressFromFields($obj);
    }

    public function updating($obj)
    {
        $obj->address = $this->createAddressFromFields($obj);
    }

    private function createAddressFromFields($obj)
    {
        $address = '';

        if ($obj->street && $obj->house_no) {
            $address = $address.$obj->street.' '.$obj->house_no.', ';
        } elseif ($obj->street) {
            $address = $address.$obj->street.', ';
        }

        if ($obj->zip_code) {
            $address = $address.$obj->zip_code.' ';
        }

        if ($obj->city) {
            $address = $address.$obj->city;
        }

        return $address;
    }

    /**
     * Handle the organization "created" event.
     *
     * @param  \App\Models\Organization  $organization
     * @return void
     */
    public function created(Organization $organization)
    {
        $this->addOpeningTimesAndOtherSettings($organization);

        $now = now()->format('Y-m-d H:i:s');

        $data = [];

        $data[] = [
            'title' => $organization->name,
            'chat_type' => 'users',
            'organization_id' => $organization->id,
            'creator_id' => null,
            'private' => false,
            'participation_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data[] = [
            'title' => $organization->name,
            'chat_type' => 'staffs',
            'organization_id' => $organization->id,
            'creator_id' => null,
            'private' => false,
            'participation_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data[] = [
            'title' => $organization->name,
            'chat_type' => 'managers',
            'organization_id' => $organization->id,
            'creator_id' => null,
            'private' => false,
            'participation_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        Conversation::insert($data);
    }
}
