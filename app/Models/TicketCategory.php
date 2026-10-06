<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    //
    protected $fillable = [
        "category_name"
    ];

     public function equipment(){
        return $this->hasMany(Equipment::class , "categoryId");
    }

    public function tickets(): HasMany
{
    return $this->hasMany(Ticket::class, 'categoryId');
}
}
