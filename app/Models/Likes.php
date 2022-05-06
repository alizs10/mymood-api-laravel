<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    use HasFactory;

    protected $table = "likes";
    
    protected $fillable = [
        "mood_id", "value"
    ];

    public function mood()
    {
        return $this->belongsTo(Mood::class, "mood_id");
    }
}
