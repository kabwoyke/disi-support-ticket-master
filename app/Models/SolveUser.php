<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SolveUser extends Authenticatable
{
    use HasFactory , Notifiable;
    protected $connection = 'mysql_solves';
    protected $table = 'solve_users';
    //


    protected $fillable = [
        'id',
        'username',
        'password',
        'role',
        'supervisor_type',
        'first_name',
        'last_name',
    ];

    protected $hidden = [
        'password',
    ];



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function question()
    {
        return $this->hasMany(Question::class, 'created_by');
    }


    public function answer(){
        $this->hasMany(Answer::class , "created_by");
    }


}
