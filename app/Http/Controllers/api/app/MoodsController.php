<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Likes;
use App\Models\Mood;
use App\Models\MoodUser;
use App\Services\MoodFilteringService;
use App\Services\PaginationService;
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
        $page = 1;
        $lastID = "";
        if (!empty($request->get("page"))) {
            $page = $request->get("page");
        }
        if (!empty($request->get("last_id"))) {
            $lastID = $request->get("last_id");
        }

        if (!empty($lastID)) {
            $page = 1;
            $moods = Mood::where("id", "<", $lastID)->orderBy("id", "desc")->get()->toArray();
        } else {
            $moods = Mood::orderBy("id", "desc")->get()->toArray();
        }

        $paginationService = new PaginationService();
        $paginate = $paginationService->paginate($moods, $page, 5);

        return response([
            'message' => "moods loaded successfully",
            'paginate' => $paginate
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
