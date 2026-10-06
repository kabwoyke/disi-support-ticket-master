<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    //

    protected $fillable = [
        'chat_id',
        'sender_type',
        'sender_id',
        'message',
        'attachment_path'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->morphTo();
    }
}
