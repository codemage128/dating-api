<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuiz extends Model
{

    protected $table = 'user_quiz';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'answer_1', 'answer_2', 'answer_3', 'answer_4'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
