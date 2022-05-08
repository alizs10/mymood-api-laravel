<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollower extends Model
{
    use HasFactory;

    protected $table = "user_followers";

    protected $fillable = [
        "user_id",
        "follower_user_id"
    ];

    // public function user()
    // {
    //     return $this->belongsToMany(User::class);
    // }
}
