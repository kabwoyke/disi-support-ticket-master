<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Desk extends Model
{
    //

    protected $fillable = [
        'desk_name'
    ];

    public function ticket(){
        return $this->hasMany(Ticket::class , "deskId");
    }
}
