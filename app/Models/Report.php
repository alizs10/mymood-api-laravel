<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "mood_id",
        "status"
    ];

    public function mood()
    {
        return $this->belongsTo(Mood::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
