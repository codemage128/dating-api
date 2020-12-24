<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'pronoun', 'looking', 'birthday', 'bio', 'sign', 'agrees_count',
        'education', 'job', 'location', 'height', 'notifications', 'distance', 'age_range'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime'
    ];

    protected $appends = [
        'sign_title', 'images', 'primary_image', 'age_range_min', 'age_range_max'
    ];

    const SIGNS = [
        1 => [
            'title' => 'aries',
            'element' => 'fire'
        ],
        2 => [
            'title' => 'taurus',
            'element' => 'earth'
        ],
        3 => [
            'title' => 'gemini',
            'element' => 'air'
        ],
        4 => [
            'title' => 'cancer',
            'element' => 'water'
        ],
        5 => [
            'title' => 'leo',
            'element' => 'fire'
        ],
        6 => [
            'title' => 'virgo',
            'element' => 'earth'
        ],
        7 => [
            'title' => 'libra',
            'element' => 'air'
        ],
        8 => [
            'title' => 'scorpio',
            'element' => 'water'
        ],
        9 => [
            'title' => 'sagittarius',
            'element' => 'fire'
        ],
        10 => [
            'title' => 'capricorn',
            'element' => 'earth'
        ],
        11 => [
            'title' => 'aquarius',
            'element' => 'air'
        ],
        12 => [
            'title' => 'pisces',
            'element' => 'water'
        ]
    ];

    const SIGNS_COMPATIBILITY = [
        'aries' => [
            'most' => ['leo', 'sagittarius'],
            'second' => ['cancer', 'scorpio', 'capricorn']
        ],
        'taurus' => [
            'most' => ['scorpio', 'cancer'],
            'second' => ['virgo', 'capricorn', 'pisces']
        ],
        'gemini' => [
            'most' => ['sagittarius', 'aquarius'],
            'second' => ['aries', 'leo', 'libra']
        ],
        'cancer' => [
            'most' => ['capricorn', 'taurus'],
            'second' => ['virgo', 'scorpio', 'pisces']
        ],
        'leo' => [
            'most' => ['aries', 'gemini'],
            'second' => ['libra', 'sagittarius', 'aquarius',]
        ],
        'virgo' => [
            'most' => ['pisces', 'cancer'],
            'second' => ['scorpio', 'capricorn', 'taurus']
        ],
        'libra' => [
            'most' => ['aries', 'sagittarius'],
            'second' => ['aquarius', 'leo', 'gemini']
        ],
        'scorpio' => [
            'most' => ['taurus', 'cancer'],
            'second' => ['virgo', 'capricorn', 'pisces']
        ],
        'sagittarius' => [
            'most' => ['gemini', 'aries'],
            'second' => ['leo', 'libra', 'aquarius']
        ],
        'capricorn' => [
            'most' => ['taurus', 'cancer'],
            'second' => ['virgo', 'scorpio', 'pisces']
        ],
        'aquarius' => [
            'most' => ['leo', 'sagittarius'],
            'second' => ['aries', 'gemini', 'libra']
        ],
        'pisces' => [
            'most' => ['virgo', 'taurus'],
            'second' => ['cancer', 'scorpio', 'capricorn']
        ],
    ];

    public function tokens()
    {
        return $this->hasMany('App\UserToken');
    }

    public function likes() {
        return $this->hasMany('App\UserLike', 'like_id');
    }

    public function liked() {
        return $this->hasMany('App\UserLike');
    }

    public function createTokenFromRequest($request) {
        $deviceId = $request->get('uuid', '');
        if(empty($deviceId)) return false;
        $token = UserToken::create([
            'user_id' => $this->id,
            'device_id' => $deviceId,
            'token' => str_random(60),
            'expires_at' => Carbon::now()->addDays(30)
        ]);
        return $token;
    }

    public function getSignId($sign) {
        return array_search(strtolower($sign), array_column(self::SIGNS, 'title'))+1;
    }

    public function getSignTitleAttribute() {
        return self::SIGNS[$this->sign]['title'];
    }

    public function getCompatibilities() {
        return self::SIGNS_COMPATIBILITY[$this->sign_title];
    }

    public function getImagesAttribute() {
        $id = $this->id;
        $result = Cache::rememberForever('user-images-'.$id, function() use ($id) {
            $files = Storage::files('user_images/'.$id);
            $urlsArray = [];
            foreach ($files as $file) {
                $urlsArray[] = Storage::url($file);
            }
            return $urlsArray;
        });
        return $result;
    }

    public function getPrimaryImageAttribute() {
        $id = $this->id;
        $result = Cache::rememberForever('user-primary-image-' . $id, function() use ($id) {
            $files = Storage::files('user_images/'.$id);
            if(empty($files)) {
                return "";
            }
            return Storage::url($files[0]);
        });

        return $result;
    }

    public function getAgeRangeMinAttribute() {
        if(empty($this->age_range)) return "18";
        $userRange = explode('-', $this->age_range);
        return $userRange[0];
    }

    public function getAgeRangeMaxAttribute() {
        if(empty($this->age_range)) return "60";
        $userRange = explode('-', $this->age_range);
        return $userRange[1];
    }

    public function storeProfileImages($images) {
        Cache::forget('user-primary-image-'.$this->id);
        Cache::forget('user-images-'.$this->id);
        foreach ($images as $key => $image) {
            $fname = $key+1 . '.' . $image->getClientOriginalExtension();
            Storage::putFileAs('user_images/'.$this->id, $image, $fname);
        }
        return true;
    }
}
