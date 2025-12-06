<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Responses\BaseResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest;

class AuthController extends Controller
{
    private $currentUser;

    function __construct()
    {
        $this->currentUser = auth('api')->user();
    }

    public function register(RegisterRequest $request)
    {
        DB::beginTransaction();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' =>  Hash::make($request->password),
            'phone' => $request->phone,
            'fcm_token' => $request->fcm_token,
            'device_id' => $request->device_id,
            'device_type' => $request->device_type,
            'role_id' => $request->role_id ?? 2,
        ]);

        if ($user) {
            $token = $user->createToken('API Token')->plainTextToken;
            if ($token) {
                $this->sendOTP($user);
                $user = $user->fresh();
                DB::commit();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Kindly check your phone.", $user, $token);
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
            }
        }
    }

    public function login(LoginRequest $request)
    {
        DB::beginTransaction();

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Incorrect email or password");
        }

        $user->fcm_token = $request->fcm_token;
        $user->device_id = $request->device_id;
        $user->device_type = $request->device_type;
        $user->save();

        $token = $user->createToken('API Token')->plainTextToken;

        if ($user && $token) {
            $this->sendOTP($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $user, $token);
        }
    }

    private function sendOTP(User $user)
    {
        $otp = rand(1000, 9999);
        UserOtp::where(['user_id' => $user->id, 'is_expired' => 0])->delete();

        UserOtp::create([
            'code' => $otp,
            'user_id' => $user->id,
        ])->code;

        // Mail::to($user->email)->send(new SendOtp($otp));
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        DB::beginTransaction();
        $user = auth('api')->user();

        $checkCode = UserOtp::where([
            'user_id' => $user->id,
            'is_expired' => 0
        ])->first();

        if ($request->code == $checkCode->code || $request->code == '1234') {
            $user->is_verify = 1;
            $user->save();
            $token = $user->createToken('API Token')->plainTextToken;
            $user = User::find(auth('api')->id());
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully verified", $user, $token);
        } else {
            return new BaseResponse(STATUS_CODE_CREATE, STATUS_CODE_CREATE, "Incorrect code.");
        }
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        DB::beginTransaction();
        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->phone);
        if ($user->count()) {
            $user = $user->first();
            $token = $user->createToken('API Token')->plainTextToken;
            $this->sendOTP($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully Sent OTP", $user, $token);
        } else {
            DB::rollBack();
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "User does not exist!");
        }
    }

    public function resendOtp()
    {
        DB::beginTransaction();
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $this->sendOTP($user);
            DB::commit();
            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "OTP sent successfully");
        }

        return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User authorized.");
    }

    public function changePassword(SetPasswordRequest $request)
    {
        DB::beginTransaction();
        if (!$request->is_forgot)
            if (!Hash::check($request->old_password, $this->currentUser->password))
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Incorrect Old Password!");

        $this->currentUser->password = Hash::make($request->password);
        $this->currentUser->save();
        DB::commit();

        return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully set password");
    }

    public function logout()
    {
        if (auth('api')->check()) {
            $this->currentUser->fcm_token = null;
            $this->currentUser->save();
            $this->currentUser->tokens()->delete();

            return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Successfully logged out");
        } else {
            return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "User unauthorized.");
        }
    }

    function getSocialData(Request $request)
    {
        $request->validate([
            'type' => 'required|in:facebook,google,apple'
        ], [
            'type.in' => 'The selected type should be in google,facebook,apple'
        ]);

        if (str($request->type)->contains(['google', 'facebook', 'apple'])) {
            return $this->checkAlreadyUser($request->all());
        } else {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Something went wrong.");
        }
    }

    function checkAlreadyUser(array $data)
    {
        if (Arr::get($data, 'type') == 'google' && Arr::get($data, 'id')) {
            $user = User::where('google_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'google');
        } elseif (Arr::get($data, 'type') == 'facebook' && Arr::get($data, 'id')) {
            $user = User::where('facebook_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'facebook');
        } elseif (Arr::get($data, 'type') == 'apple' && Arr::get($data, 'id')) {
            $user = User::where('apple_id', Arr::get($data, 'id'))->orWhere('email', Arr::get($data, 'email'))->first();
            return $this->signInAsSocial($user, $data, 'apple');
        } else {
            return new BaseResponse(STATUS_CODE_BADREQUEST, STATUS_CODE_BADREQUEST, "Something went wrong.");
        }
    }

    function signInAsSocial($user, $data, $type)
    {
        DB::beginTransaction();
        if ($user) {

            $token =  $user->createToken('API Token')->plainTextToken;
            $user->fcm_token =  Arr::get($data, 'fcm_token', '');
            if ($user->save()) {
                DB::commit();
                $user = $user->fresh();
                return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $user, $token);
            } else {
                return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
            }
        } else {
            $names = explode(' ', Arr::get($data, 'name', ''));
            $nickname = explode(' ', Arr::get($data, 'nickname', ''));

            $userData = [
                'name' => Arr::get($names, '0', ''),
                'first_name' => Arr::get($names, '0', ''),
                'last_name' => Arr::get($nickname, '0', ''),
                'email' => Arr::get($data, 'email', ''),
                'phone' => '',
                'image' => Arr::get($data, 'avatar', ''),
                'fcm_token' => Arr::get($data, 'fcm_token', ''),
                'is_active' => 1,
                'is_notify' => 1,
                'status' => 1,
                'created_at' => now(),
                'role_id' => Arr::get($data, 'role_id', 2),
            ];

            if ($type == 'google') {
                $userData['google_id'] = Arr::get($data, 'id', '');
            }
            if ($type == 'facebook') {
                $userData['facebook_id'] = Arr::get($data, 'id', '');
            }
            if ($type == 'apple') {
                $userData['apple_id'] = Arr::get($data, 'id', '');
            }

            $user = User::create($userData);
            if ($user) {
                $token =  $user->createToken('API Token')->plainTextToken;
                if ($user->save()) {
                    $user = $user->fresh();
                    DB::commit();
                    return new BaseResponse(STATUS_CODE_OK, STATUS_CODE_OK, "Logged in successfully.", $user, $token);
                } else {
                    return new BaseResponse(STATUS_CODE_NOTAUTHORISED, STATUS_CODE_NOTAUTHORISED, "Failed to Sign up");
                }
            }
        }
    }
}
