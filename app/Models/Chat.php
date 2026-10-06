<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Chat extends Model
{
    //

    protected $fillable = [
        'user_id',
        'support_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function support()
    {
        return $this->belongsTo(SupportTeam::class, 'support_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
