<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForbiddenWord extends Model
{
    use HasFactory;

    protected $table = "forbidden_words";

    protected $fillable = [
        "word",
        "status"
    ];
}
