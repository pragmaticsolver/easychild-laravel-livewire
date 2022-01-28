<?php

namespace App\Actions\User;

use App\Models\ParentLink;
use App\Models\User;
use App\Notifications\NewParentSignupNotification;
use Lorisleiva\Actions\Concerns\AsObject;

class NewParentCreateAction
{
    use AsObject;

    public function handle(ParentLink $parentLink, $data = [])
    {
        $parent = User::firstOrCreate(
            [
                'email' => $parentLink->email,
            ],
            [
                'role' => 'Parent',
            ]
        );

        $parent->childrens()->syncWithoutDetaching([$parentLink->child_id]);

        $parent->notify(new NewParentSignupNotification($parent, $parentLink));
    }
}
