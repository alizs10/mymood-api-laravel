<?php

namespace App\Http\Controllers\api\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailServices;
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

    public function registerEmail($email)
    {
        $user = User::create([
            'name' => "کاربر",
            'email' => $email,
        ]);

        $user_name = "کاربر {$user->id}";
        $user->update(["name" => $user_name]);

        return $user;
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed'],
        ]);

        $user = User::where("email", $request->email)->first();
        $user->update(["password" => Hash::make($request->password)]);

        return response([
            "message" => "password is set successfully, ready to go",
            "email" => $user->email
        ], 200);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response([
            'message' => 'successfully logged out'
        ], 200);
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

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => "required",
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'max:16', 'confirmed']
        ]);
        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
           return response([
               "message" => "old password is incorrect"
           ], 419);
        }

        $result = $user->update(["password" => Hash::make($request->password)]);

        if ($result) {
            return response([
                "message" => "password changed successfully"
            ], 200);
        }

    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);
        $user = User::where("email", $request->email)->first();
        
        if (!$user || (empty($user->email_verified_at) && empty($user->password)) || (!empty($user->email_verified_at) && empty($user->password))) {

            if (!$user) {
                $user = $this->registerEmail($request->email);
            }

            if ($user) {
                $isVCodeSent = $this->sendVerificationCode($user);

                if ($isVCodeSent) {
                    return response([
                        'message' => 'verification code sent successfully',
                        'status' => false
                    ], 200);
                }
            }


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

    public function loginOrRegister($credentials)
    {
        $user = User::where("email", $credentials->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => "کاربر",
                'email' => $credentials->email,
                'provider_id' => $credentials->id,
                'email_verified_at' => now()
            ]);
            $user_name = "کاربر {$user->id}";
            $user->update(["name" => $user_name]);
        }

        $result = Auth::loginUsingId($user->id);
        if ($result) {
            $token = Auth::user()->createToken('login')->plainTextToken;
            return response([
                'message' => 'successfully logged in',
                'user' => Auth::user(),
                'token' => $token
            ], 200);
        }

        dd($result);
        return response([
            'message' => 'something went wrong, try again'
        ], 401);
    }


    public function sendVerificationCode($user)
    {
        $verification_code = mt_rand(100000, 999999);
        $user->update(['verification_code' => $verification_code]);

        EmailServices::SendVCode($user->email, $verification_code);

        return true;
    }

    public function checkVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|string|size:6',
        ]);

        $user = User::where("email", $request->email)->first();

        $verification_code = $user->verification_code;

        if ($verification_code === $request->verification_code) {
            $user->update(['email_verified_at' => now(), 'verification_code' => null]);

            return response([
                'message' => 'user account activated successfully',
                'email' => $user->email
            ], 200);
        }

        return response([
            'message' => 'verification code is wrong'
        ], 422);
    }



    public function redirectToGoogle()
    {
        $res = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();

        return response(
            $res
        , 200);
    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->stateless()->user();
        return $this->loginOrRegister($user);
    }
}
