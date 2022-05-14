<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Mood;
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
            "server_time" => now()
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
            $loggedUser = Auth::guard('sanctum')->user();
            if ($loggedUser) {
                Auth::setUser($loggedUser);
            }
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
            "loggedUser" => $loggedUser,
            "server_time" => now()
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

    public function stats()
    {
        $moods = Mood::all();
        $types = [
            "sad" => 0,
            "happy" => 1,
            "sick" => 2,
            "sleepy" => 3,
            "angry" => 4,
            "anxious" => 5,
            "expressionless" => 6,
            "straight_face" => 7
        ];
        $sad = count($moods->where("type", $types["sad"])->toArray());
        $happy = count($moods->where("type", $types["happy"])->toArray());
        $sick = count($moods->where("type", $types["sick"])->toArray());
        $sleepy = count($moods->where("type", $types["sleepy"])->toArray());
        $angry = count($moods->where("type", $types["angry"])->toArray());
        $anxious = count($moods->where("type", $types["anxious"])->toArray());
        $expressionless = count($moods->where("type", $types["expressionless"])->toArray());
        $straight_face = count($moods->where("type", $types["straight_face"])->toArray());

        $data = [
            $sad,
            $happy,
            $sick,
            $sleepy,
            $angry,
            $anxious,
            $expressionless,
            $straight_face,
        ];

        return response([
            "message" => "stats calculated successfully",
            "data" => $data,
            "types" => $types
        ], 200);
    }
}
