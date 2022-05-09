<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserFollower;
use App\Models\UserFollowing;
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

    public function users()
    {
        $users = User::all();
        $usersIds = [];
        foreach ($users as $user) {
            array_push($usersIds, $user->id);
        }
        return response([
            "message" => "users loaded successfuuly",
            "users" => $usersIds,

        ], 200);
    }

    public function user(Request $request, User $user)
    {
        $moods = $user->moods;
        $followers = count($user->followers);
        $followings = count($user->followings);


        if ($request->bearerToken()) {
            Auth::setUser(
                Auth::guard('sanctum')->user()
            );
        }
        $loggedUser = Auth::user();
        $isFollowed = false;

        if ($loggedUser) {
            foreach ($loggedUser->followings as $following) {
                if ($following->followed_user_id === $user->id) {
                    $isFollowed = true;
                    break;
                }
            }
        } else {
            $loggedUser = false;
        }
        return response([
            "message" => "user information loaded successfuuly",
            "user" => $user,
            "moods" => $moods,
            "followers" => $followers,
            "followings" => $followings,
            "isFollowed" => $isFollowed,
            "loggedUser" => $loggedUser
        ], 200);
    }

    public function follow(User $user_followed)
    {
        $user = Auth::user();
        $user_followed->followers()->save(new UserFollower(["follower_user_id" => $user->id]));
        $user->followings()->save(new UserFollowing(["followed_user_id" => $user_followed->id]));
        return response([
            "message" => "user followed successfuuly",
            "followers" => count($user_followed->followers)
        ], 200);
    }
    public function unfollow(User $user_unfollowed)
    {
        $user = Auth::user();
        $user->followings()->where(["followed_user_id" => $user_unfollowed->id])->delete();
        $user_unfollowed->followers()->where(["follower_user_id" => $user->id])->delete();
        return response([
            "message" => "user unfollowed successfuuly",
            "followers" => count($user_unfollowed->followers)

        ], 200);
    }
}
