<?php

namespace App\Actions\User;

use App\Models\ParentLink;
use App\Models\User;
use App\Notifications\NewChildLinkedToParentNotification;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;

class AssignParentToChildAction
{
    use AsObject;

    public function handle(User $child, User $parent)
    {
        $parentLink = ParentLink::firstOrCreate([
            'email' => $parent->email,
            'child_id' => $child->id,
        ], [
            'token' => Str::random(),
        ]);

        $successCode = 'new';

        if ($parent->getKey()) {
            $successCode = 'linked';

            $parent->childrens()->syncWithoutDetaching([$child->id]);

            $this->sendChildAttachedEmailToParent($child, $parent, $parentLink);
        } else {
            $this->sendNewParentSignupLink($parentLink);
        }

        return $successCode;
    }

    private function sendChildAttachedEmailToParent(User $child, User $parent, ParentLink $parentLink)
    {
        auth()->user()->jobs()->updateOrCreate([
            'related_type' => ParentLink::class,
            'related_id' => $parentLink->id,
            'action' => NewChildLinkedToParentNotification::class,
        ], [
            'user_ids' => [$parent->id],
            'due_at' => now()->addMinutes(5),
            'data' => [],
        ]);
    }

    private function sendNewParentSignupLink(ParentLink $parentLink)
    {
        auth()->user()->jobs()->updateOrCreate([
            'related_type' => ParentLink::class,
            'related_id' => $parentLink->id,
            'action' => NewParentCreateAction::class,
        ], [
            'user_ids' => [],
            'due_at' => now()->addMinutes(5),
            'data' => [],
        ]);
    }
}
