<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    //

    protected $fillable = [
    'subject',
    'categoryId',
    'priority',
    'equipmentId',
    'departmentId',
    'description',
    'attachment_url',
    'deskId',
    'userId',
    'status'
];


public function category(): BelongsTo
{
    return $this->belongsTo(TicketCategory::class, 'categoryId');
}

public function equipment(): BelongsTo
{
    return $this->belongsTo(Equipment::class, 'equipmentId');
}

public function department(): BelongsTo
{
    return $this->belongsTo(Department::class, 'departmentId');
}


public function ticket_assignment(){
    return $this->hasMany(TicketAssignment::class , 'ticketId');
}

public function desk(){
    return $this->belongsTo(Desk::class , 'deskId');
}

public function user(){
    return $this->belongsTo(User::class , 'userId');
}

}
