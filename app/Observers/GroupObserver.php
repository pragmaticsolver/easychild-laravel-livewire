<?php

namespace App\Observers;

use App\Models\Conversation;
use App\Models\Group;

class GroupObserver
{
    /**
     * Handle the group "created" event.
     *
     * @param  \App\App\Models\Group  $group
     * @return void
     */
    public function created(Group $group)
    {
        $now = now()->format('Y-m-d H:i:s');
        $orgId = $group->organization_id;

        $data = [];

        $data[] = [
            'title' => $group->name,
            'chat_type' => 'users',
            'organization_id' => $orgId,
            'group_id' => $group->id,
            'creator_id' => null,
            'private' => false,
            'participation_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $data[] = [
            'title' => $group->name,
            'chat_type' => 'principals',
            'organization_id' => $orgId,
            'group_id' => $group->id,
            'creator_id' => null,
            'private' => false,
            'participation_id' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        Conversation::insert($data);
    }
}
