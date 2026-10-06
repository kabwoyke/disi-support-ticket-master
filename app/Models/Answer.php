<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    //

    protected $connection = 'mysql_solves';
    protected $table = 'answers';

    protected $fillable = [
        'question_id',
        'answer_text',
        'created_by',
        'status',
        'attachment'
    ];


    public function question(){
        return $this->belongsTo(Question::class , 'question_id');
    }

    public function author(){
        return $this->belongsTo(SolveUser::class , "created_by");
    }
}
