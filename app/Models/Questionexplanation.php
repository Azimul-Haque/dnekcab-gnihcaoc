<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionexplanation extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id', // <--- এটি যুক্ত করুন
        'explanation', // <--- এটি যুক্ত করুন
    ];

    public function qustion(){
        return $this->belongsTo('App\Models\Question');
    }
}
