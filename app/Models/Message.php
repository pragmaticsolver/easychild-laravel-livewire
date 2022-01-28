<?php

namespace App\Models;

use App\Traits\Uuidable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Message extends Model
{
    use Uuidable, HasFactory;

    protected $guarded = [];

    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id' => 'integer',
    ];

    protected $touches = ['conversation'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMessageWithUser($query)
    {
        $query->addSelect([
            'sender_name' => DB::table('users')
                ->whereColumn('users.id', 'messages.sender_id')
                ->select('users.given_names')
                ->limit(1),
            'sender_full_name' => DB::table('users')
                ->whereColumn('users.id', 'messages.sender_id')
                ->selectRaw('CONCAT(users.given_names, " ", users.last_name)')
                ->limit(1),
            'sender_avatar' => DB::table('users')
                ->whereColumn('users.id', 'messages.sender_id')
                ->select('users.avatar')
                ->limit(1),
            'role' => DB::table('users')
                ->whereColumn('users.id', 'messages.sender_id')
                ->select('users.role')
                ->limit(1),
        ]);
    }

    public function scopeAddReadAndSeenBy($query, $userIds = [])
    {
        $query->addSelect([
            // 'received_by' => DB::table('messages as m1')
            // ->whereColumn('m1.conversation_id', 'messages.conversation_id')
            // ->join('conversation_notifications', 'conversation_notifications.conversation_id', 'm1.conversation_id')
            // ->whereIn('conversation_notifications.user_id', $userIds)
            // ->whereRaw('conversation_notifications.read_at >= m1.created_at')
            // ->selectRaw('COUNT(DISTINCT(conversation_notifications.user_id))')
            // ->limit(1),
            'received_by' => DB::table('conversation_notifications')
                ->whereIn('conversation_notifications.user_id', $userIds)
                ->whereRaw('conversation_notifications.read_at >= messages.created_at')
                ->selectRaw('COUNT(DISTINCT(conversation_notifications.user_id))')
                ->limit(1),
            'read_by' => DB::table('conversation_notifications')
                ->whereColumn('conversation_notifications.conversation_id', 'messages.conversation_id')
                ->whereRaw('conversation_notifications.read_at >= messages.created_at')
                ->selectRaw('COUNT(DISTINCT(conversation_notifications.user_id))')
                ->limit(1),
        ]);
    }

    public function getAttachmentUrl()
    {
        if($this->attachment) {
            return URL::signedRoute('private-files', [
                'path' => storage_path('app/attachments/' . $this->conversation_id . '/'.$this->uuid.'/'.$this->attachment),
                'model' => 'message',
                'uuid' => $this->uuid,
            ]);
        }

        return $this->attachment;
    }
}
