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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
