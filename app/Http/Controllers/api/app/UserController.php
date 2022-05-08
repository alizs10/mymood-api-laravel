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
        $followers = count($user->followers);
        $followings = count($user->followings);

        return response([
            "message" => "user profile information loaded successfuuly",
            "user" => $user,
            "moods" => $moods,
            "followers" => $followers,
            "followings" => $followings,
        ], 200);
    }

    public function update(Request $request)
    {
        $request->validate([
            "bio" => "nullable|string|max:70"
        ]);

        $user = Auth::user();
        $user->update(["bio" => $request->bio]);

        return response([
            "message" => "bio updated successfully",
            "user" => $user
        ], 200);
    }
}
