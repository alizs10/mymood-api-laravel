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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
