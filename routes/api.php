<?php

use App\Http\Controllers\api\app\MoodsController;
use App\Http\Controllers\api\auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentications
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/check-email', [AuthController::class, 'checkEmail']);
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

//google login
Route::get('/auth/google', [AuthController::class, "redirectToGoogle"]);
Route::get('/auth/google/callback', [AuthController::class, "handleGoogleCallback"]);

//public
Route::get('/moods', [MoodsController::class, 'index']);


//private
Route::post('/moods/store', [MoodsController::class, 'store'])->middleware('auth:sanctum');
Route::get('/moods/{mood}/like', [MoodsController::class, 'like'])->middleware('auth:sanctum');
Route::get('/moods/{mood}/unlike', [MoodsController::class, 'unlike'])->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
