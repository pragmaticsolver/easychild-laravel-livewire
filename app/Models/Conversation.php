<?php

namespace App\Models;

use App\Traits\HasSearchScope;
use App\Traits\HasUsersThreads;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;
    use HasUsersThreads;
    use HasSearchScope;

    protected $guarded = [];

    protected $casts = [
        'private' => 'boolean',
        'organization_id' => 'integer',
        'group_id' => 'integer',
        'creator_id' => 'integer',
        'participation_id' => 'integer',
        'custom_participants' => 'array',
    ];

    protected $searchConfig = [
        'table' => 'conversations',
        'cols' => [
            'conversations.title',
            'conversations.alt_title',
        ],
    ];

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->belongsTo(Message::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function scopeWithLastMessage($query, $loadWith = true)
    {
        $query->addSelect(['last_message_id' => Message::select('id')
            ->whereColumn('conversation_id', 'conversations.id')
            ->whereNull('deleted_at')
            ->latest()
            ->take(1),
        ]);

        if ($loadWith) {
            $query->with('lastMessage');
        }
    }

    public function scopeWithLastReadAt($query)
    {
        $defaultLast = now()->subYear(1)->format('Y-m-d H:i:s');

        $query->addSelect(['last_read_at' => DB::table('conversations as cc')
            ->whereColumn('cc.id', 'conversations.id')
            ->leftJoin('conversation_notifications', 'conversation_notifications.conversation_id', 'cc.id')
            ->selectRaw("COALESCE(conversation_notifications.read_at, '$defaultLast')")
            ->latest('conversation_notifications.read_at')
            ->take(1),
        ]);
    }

    public function unreadMessages()
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id')
            ->where('messages.sender_id', '!=', auth()->id())
            ->where('messages.created_at', '>=', $this->last_read_at);
    }

    public function notifications()
    {
        return $this->hasMany(ConversationNotification::class, 'conversation_id', 'id');
    }

    public function conversationTitle()
    {
        $title = '';

        if ($this->chat_type == 'custom') {
            $title = $this->title;

            $title .= ' &bull; <small class="align-top">&#128274;</small>';
        } elseif ($this->private) {
            if ($this->creator_id == auth()->user()->id) {
                if (Str::startsWith($this->title, 'messages.roles.')) {
                    $title = trans($this->title);
                } else {
                    $title = $this->title;
                }
            } else {
                $title = $this->alt_title;
            }

            $title .= ' &bull; <small class="align-top">&#128274;</small>';
        } else {
            $title = $this->title;
            $title .= ' &bull; ';

            if ($this->chat_type == 'users') {
                $title .= trans("messages.roles.users");
            } elseif ($this->chat_type == 'principals') {
                $title .= trans("messages.roles.principals");
            } elseif ($this->chat_type == 'admins') {
                $title .= trans("messages.roles.admins");
            } elseif ($this->chat_type == 'managers') {
                $title .= trans("messages.roles.managers");
            } elseif ($this->chat_type == 'staffs') {
                $title .= trans("messages.roles.staffs");
            }
        }

        return $title;
    }
}
