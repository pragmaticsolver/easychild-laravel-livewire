<?php

namespace App\Actions\User;

use App\Models\CalendarEvent;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\ConversationNotification;
use App\Models\Information;
use App\Models\InformationNotification;
use App\Models\Message;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserLog;
use Lorisleiva\Actions\Concerns\AsObject;

class RemoveUserRelatedDataWhenDeletedAction
{
    use AsObject;

    private function clearUserRoleRelations(User $user)
    {
        Message::query()
            ->whereIn('sender_id', $user->parents()->pluck('users.id'))
            ->delete();

        $user->parents()->sync([]);

        $user->parentLinks()->delete();

        // delete all schedules
        $user->schedules()->delete();

        // delte all user logs
        UserLog::query()
            ->where('user_id', $user->id)
            ->delete();

        // delete all notes
        $user->notes()->delete();
    }

    private function clearParentRoleRelations(User $user)
    {
        $user->childrens()->sync([]);

        $user->childLinks()->delete();
    }

    public function handle(User $user)
    {
        $user->groups()->sync([]);

        if ($user->isUser()) {
            $this->clearUserRoleRelations($user);
        }

        if ($user->isParent()) {
            $this->clearParentRoleRelations($user);
        }

        $user->jobs()->delete();
        $user->notifications()->delete();

        // delete all messages
        Message::query()
            ->where('sender_id', $user->id)
            ->delete();

        ConversationNotification::query()
            ->where('user_id', $user->id)
            ->delete();

        Contact::query()
            ->where('user_id', $user->id)
            ->delete();

        InformationNotification::query()
            ->where('user_id', $user->id)
            ->delete();

        Information::query()
            ->where('creator_id', $user->id)
            ->delete();

        // delete all private conversation
        Conversation::query()
            ->where('creator_id', $user->id)
            ->delete();

        Schedule::query()
            ->where('dealt_by', $user->id)
            ->update([
                'dealt_by' => null,
            ]);

        // clear creator id from calendar events
        CalendarEvent::query()
            ->where('creator_id', $user->id)
            ->update([
                'creator_id' => null,
            ]);

        UserLog::query()
            ->where('triggred_id', $user->id)
            ->update([
                'triggred_id' => null,
            ]);

        Message::query()
            ->where('sender_id', $user->id)
            ->delete();
    }
}
