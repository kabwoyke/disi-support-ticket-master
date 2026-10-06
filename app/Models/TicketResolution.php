<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketResolution extends Model
{

    //
    protected $fillable = [
        'ticket_assignment_id',
        'resolved_by'
    ];

    public function ticket_assignment(){
        return $this->belongsTo(TicketAssignment::class ,'ticket_assignment_id');
    }

     public function support_team(){
        return $this->belongsTo(SupportTeam::class ,'resolved_by');
    }
}
