<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollowing extends Model
{
    use HasFactory;

    protected $table = "user_followings";

    protected $fillable = [
        "user_id",
        "followed_user_id"
    ];

    public function moods($orderBy = ["column" => "created_at", "sort" => "desc"])
    {
        return $this->hasMany(Mood::class, "user_id", "followed_user_id")->orderBy($orderBy["column"], $orderBy["sort"]);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
