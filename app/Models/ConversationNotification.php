<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationNotification extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'conversation_id' => 'integer',
        'user_id' => 'integer',
    ];

    protected $dates = [
        'read_at',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
