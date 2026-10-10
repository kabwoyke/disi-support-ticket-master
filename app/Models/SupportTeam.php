<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SupportTeam extends Authenticatable
{
    use Notifiable, \App\Models\Concerns\HasAvatar;
    //

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'email',
        'password',
        'ticket_category_id',
        'available',
        'profile_picture',
        'max_ticket_capacity',
        'ticket_count',
    ];


      protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'available' => 'boolean',
        ];
    }

public function ticket_assignment(){
    return $this->hasMany(TicketAssignment::class , 'teamId');
}

public function category(){
    return $this->belongsTo(TicketCategory::class , 'ticket_category_id');
}

public function ticket_resolution(){
    return $this->hasMany(TicketResolution::class , 'resolved_by');
}

}
