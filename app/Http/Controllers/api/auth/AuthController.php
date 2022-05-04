<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as FacadesPassword;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);


        $credentials = $request->only(['email', 'password']);
        $result = Auth::attempt($credentials);

        if ($result) {
            $token = Auth::user()->createToken('login')->plainTextToken;
            return response([
                'message' => 'successfully logged in',
                'user' => Auth::user(),
                'token' => $token
            ], 200);
        }

        return response([
            'message' => 'email or password is wrong'
        ], 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed'],
        ]);

        $user = User::create([
            'name' => "کاربر",
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user_name = "کاربر {$user->id}";

        $user->update(["name" => $user_name]);

        if ($user) {
            return response([
                'message' => 'user created successfully',
                'user' => $user,
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    public function logout()
    {
        $result = Auth::user()->tokens()->delete();
        if ($result) {
            return response([
                'message' => 'successfully logged out'
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 500);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = FacadesPassword::sendResetLink(
            $request->only('email')
        );


        if ($status === FacadesPassword::RESET_LINK_SENT) {
            return response([
                'message' => 'reset password link is sent successfully',
                'status' => true
            ], 200);
        }

        return response([
            'message' => 'something went wrong',
            'status' => false
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed']
        ]);

        $status = FacadesPassword::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === FacadesPassword::PASSWORD_RESET) {
            return response([
                'message' => 'password changed successfully',
                'status' => true
            ], 200);
        }

        return response([
            'message' => 'something went wrong',
            'status' => false
        ], 200);
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $user = User::where("email", $request->email)->first();

        if (!$user) {
            return response([
                'message' => 'user does not exists',
                'status' => false
            ], 200);
        }

        return response([
            'message' => 'user exists',
            'status' => true
        ], 200);
    }

    public function loginOrRegister($user)
    {
        $user = User::where("email", $user->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => "کاربر",
                'email' => $user->email,
                'provider_id' => $user->provider_id
            ]);
            $user->update(["name" => "کاربر" + " " + $user->id]);
        }
       
        $result = Auth::login($user);
        if ($result) {
            $token = Auth::user()->createToken('login')->plainTextToken;
            return response([
                'message' => 'successfully logged in',
                'user' => Auth::user(),
                'token' => $token
            ], 200);
        }

        return response([
            'message' => 'something went wrong, try again'
        ], 401);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();

        $this->loginOrRegister($user);
    }
}
