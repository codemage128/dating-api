<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLike extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'like_id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function liked() {
        return $this->belongsTo('App\User', 'like_id');
    }
}
