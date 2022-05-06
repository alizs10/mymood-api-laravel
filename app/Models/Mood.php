<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mood extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "mood",
        "type",
        "user_id",
        "status"
    ];

    protected $appends = ["likes_value", "users_likes_ids"];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasOne(Likes::class, "mood_id");
    }

    public function getLikesValueAttribute()
    {
        return $this->likes->value;
    }

    public function usersLikes()
    {
        return $this->belongsToMany(User::class)->using(MoodUser::class);
    }

    public function getUsersLikesIdsAttribute()
    {
        $users_ids = [];
        foreach ($this->usersLikes as $liker) {
            array_push($users_ids, $liker->id);
        }

        return $this->attributes['users_likes_ids'] = $users_ids;
    }
}
