<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'provider_id',
        'avatar',
        'bio',
        'password',
        'is_admin',
        'verification_code',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email',
        'provider_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function moods()
    {
        return $this->hasMany(Mood::class, "user_id")->orderBy("created_at", "desc");
    }

    public function moodsLiked()
    {
        return $this->belongsToMany(Mood::class)->using(MoodUser::class);
    }
    public function followers()
    {
        return $this->hasMany(UserFollower::class, "follower_user_id");
    }
    public function followings()
    {
        return $this->hasMany(UserFollowing::class, "followed_user_id");
    }

    public function getUsersLikesValueAttribute()
    {
        return $this->moodsLiked;
    }
}
