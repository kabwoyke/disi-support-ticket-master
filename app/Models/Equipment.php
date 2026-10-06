<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    //

    public $table = "equipments";

    protected $fillable = [
        "categoryId",
        "name"
    ];

    public function category(){
        return $this->belongsTo(TicketCategory::class , "categoryId");
    }

    public function tickets(): HasMany
{
    return $this->hasMany(Ticket::class, 'equipmentId');
}
}
