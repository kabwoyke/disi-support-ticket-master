<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    //

    public $table = "departments";
    
    protected $fillable = [
        'department_name'
    ];

    public function tickets(): HasMany
{
    return $this->hasMany(Ticket::class, 'departmentId');
}
}
