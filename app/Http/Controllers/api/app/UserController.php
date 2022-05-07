<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $moods = $user->moods;

        return response([
            "message" => "user profile information loaded successfuuly",
            "user" => $user,
            "moods" => $moods
        ], 200);
    }
}
