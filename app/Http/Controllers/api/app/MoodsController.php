<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use App\Models\MoodUser;
use App\Services\MoodFilteringService;
use App\Services\MoodsServices;
use App\Services\PaginationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->bearerToken()) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                Auth::setUser($user);
            }
        }
        $user = Auth::user();

        $page = 1;
        $followers = "false";
        $lastID = "";
        $orderBy = "lastest";


        if (!empty($request->get("last_id"))) {
            $lastID = $request->get("last_id");
        }
        if (!empty($request->get("order_by"))) {
            $orderBy = $request->get("order_by");
        }
        if (!empty($request->get("followers"))) {
            $followers = $request->get("followers");
        }



        if ($followers === "true" && $user) {
            $moods = new Collection();
            $followings = $user->followings;
            foreach ($followings as $following) {
                $moods = $moods->merge($following->moods);
            }
        } else {
            $moods = Mood::all();
        }

        if ($orderBy === "moodest") {
            if (!empty($request->get("page"))) {
                $page = $request->get("page");
            }
            $lastID = "";
            $moods = $moods->sortByDesc("likes_value");
        } else {
            $moods = $moods->sortByDesc("id");
        }

        if (!empty($lastID)) {
            $moods = $moods->where("id", "<", $lastID)->sortByDesc("id");
        }



        $moods = $moods->toArray();

        $paginationService = new PaginationService();
        $paginate = $paginationService->paginate($moods, $page, 20);

        return response([
            'message' => "moods loaded successfully",
            'paginate' => $paginate,
            'order_by' => $orderBy,
            'followers' => $followers,
            'last_id' => $lastID
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            "mood" => "required|string|min:3|max:700",
            "type" => "required|in:0,1,2,3,4,5,6,7",
        ]);

        $inputs = $request->only(["mood", "type"]);
        $inputs["user_id"] = Auth::user()->id;

        $moodFilteringService = new MoodFilteringService($inputs["mood"]);
        $inputs["mood"] = $moodFilteringService->filter();

        $mood = Mood::create($inputs);
        $likes = $mood->likes()->create([
            'value' => 0,
        ]);

        return response([
            "message" => "mood created successfully",
            "mood" => $mood
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mood $mood)
    {
        $user = Auth::user();
        $res = $this->isUserOwnsMood($user, $mood);

        if ($res) {
            $mood->delete();

            return response([
                "mood deleted successfully"
            ], 200);
        }
    }

    public function like(Mood $mood)
    {
        $user = Auth::user();
        $isLikedBefore = MoodUser::where(["user_id" => $user->id, "mood_id" => $mood->id])->first();
        if (!$isLikedBefore) {
            $mood->usersLikes()->attach($user);
            $likesValue = count($mood->usersLikes->toArray());
            $mood->likes()->update(["value" => $likesValue]);
        }

        return response([
            "message" => "mood liked successfully",
            "mood" => $mood
        ], 200);
    }

    public function unlike(Mood $mood)
    {
        $user = Auth::user();
        $isLiked = MoodUser::where(["user_id" => $user->id, "mood_id" => $mood->id])->first();
        if ($isLiked) {
            $mood->usersLikes()->detach($user);
            $likesValue = count($mood->usersLikes->toArray());
            $mood->likes()->update(["value" => $likesValue]);
        }

        return response([
            "message" => "mood unliked successfully",
            "mood" => $mood
        ], 200);
    }


    public function isUserOwnsMood($user, $mood)
    {
        if ($user->id === $mood->user_id) {
            return true;
        }

        return response([
            "message" => "did not found mood"
        ], 419);
    }
}
