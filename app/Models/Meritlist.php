<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meritlist extends Model
{
    protected $fillable = [
        'exam_id',
        'question_id',
    ];
    
    public function exam(){
        return $this->belongsTo('App\Models\Exam');
    }

    public function course(){
        return $this->belongsTo('App\Models\Course');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
}
