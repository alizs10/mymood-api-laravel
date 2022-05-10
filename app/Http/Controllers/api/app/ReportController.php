<?php

namespace App\Http\Controllers\api\app;

use App\Http\Controllers\Controller;
use App\Models\Mood;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function report(Request $request, Mood $mood)
    {
        if ($request->bearerToken()) {
            Auth::setUser(
                Auth::guard('sanctum')->user()
            );
        }
        $user_id = null;
        if (Auth::user()) {
            $user_id = Auth::user()->id;
        }

        $inputs = [
            "mood_id" => $mood->id,
            "user_id" => $user_id,
            "status" => 0
        ];

        $report = Report::create($inputs);

        return response([
            "report received successfully, thanks!"
        ], 200);
    }
}
