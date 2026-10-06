<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAssignment extends Model
{
    //

    protected $fillable = [
        'ticketId',
        'teamId',
        'status'
    ];

    public function ticket(){
        return $this->belongsTo(Ticket::class , 'ticketId');
    }

    public function support_team(){
        return $this->belongsTo(SupportTeam::class , 'teamId');
    }

    public function ticket_resolution(){
        $this->hasMany(TicketResolution::class , 'ticket_assignment_id');
    }

}
