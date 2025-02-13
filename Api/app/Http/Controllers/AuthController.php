<?php

namespace App\Http\Controllers;

use App\Models\RolePermission;
use App\Models\User;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'resetPassword', 'verifyRegister','resetPasswordAdmin','logoutUserOtherUser','verifyLogin','mobileLoginUser']]);
    }

    public function login(Request $request): JsonResponse
    {
        if(filter_var($request->get('user'), FILTER_VALIDATE_EMAIL)){
            $request->merge(['email' => $request->get('user')]);

        }else if(is_numeric($request->get('user'))){
            $request->merge(['phone_number' => $request->get('user')]);

        }else{
            return $this::errorReturn([], 'Please enter a valid email address or phone number', ResponseAlias::HTTP_BAD_REQUEST);
        }

        $validator = ValidationService::loginValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }

        $user = User::where(function($query) use ($request) {
            $query->where('email', $request->email)
                ->orWhere('phone_number', $request->phone_number);
        })->first();

        if (!$user) {
            return $this->errorReturn([], 'User not found', ResponseAlias::HTTP_NOT_FOUND);
        }

        if ($user->role_id != 1 && $user->role_id != 2 && $user->role_id != 3) {
            return $this::errorReturn([], 'You can not access the system', ResponseAlias::HTTP_BAD_REQUEST);
        }

        $credentials = $validator->validated();

        if (!$token = auth()->attempt($credentials)) {
            return $this::errorReturn([], 'Invalid credentials', ResponseAlias::HTTP_BAD_REQUEST);
        }

        if(!auth()->user()->is_active) {
            return $this::errorReturn([], 'inactive_user', ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $user = User::query()->where('id', auth()->user()->id)->first();

        if ($user->token != null) {
            JWTAuth::manager()->invalidate(new \Tymon\JWTAuth\Token($user->token), $forceForever = false);
        }

        $user->token =$token;
        $user->save();

        $return_data = $this->createNewToken($token);
        return $this::successReturn($return_data->original, 'Logged in successfully', ResponseAlias::HTTP_OK);
    }

    protected function createNewToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user_ref' => auth()->user()->user_ref,
            'name' => auth()->user()->full_name,
            'phone_number' => auth()->user()->phone_number,
            'email' => auth()->user()->email,
            'status' => auth()->user()->is_active,
            'agency_id' => auth()->user()->agency_id,
            'agency_name' => User::getUserAgencyName(auth()->user()->id),
            'user_permissions' => RolePermission::getUserPermissions(auth()->user()->role_id,auth()->user()->agency_id),
        ]);
    }

//    public function register(Request $request): JsonResponse
//    {
//        try {
//            $validator = ValidationService::registerValidator(request()->all());
//            if ($validator->fails()) {
//                return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
//            }
//            $verifiedOtp = SMSServices::verifyPhoneNumberOtp(request()->all());
//
//            if($verifiedOtp->status) {
//                $credentials = User::createUser($request);
//                if (!$token = auth()->attempt($credentials)) {
//                    return $this::errorReturn([], 'Invalid credentials', ResponseAlias::HTTP_BAD_REQUEST);
//                }
//                $return_data = $this->createNewToken($token);
//                UserLoggingLog::LogUserLogin(auth()->user()->id);
//                return $this::successReturn($return_data->original, 'Registration and Logged in successfully', ResponseAlias::HTTP_OK);
//            }
//            return $this::errorReturn([], $verifiedOtp->message,ResponseAlias::HTTP_BAD_REQUEST);
//        }catch (\Exception $e) {
//            Log::error($e);
//            return $this::errorReturn([], 'Registration Unsuccessful', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
//        }
//    }

    public function logout(): JsonResponse
    {
        try {
            $user = User::query()->where('id', auth()->user()->id)->first();
            $user->token = null;
            $user->save();
            auth()->logout();
            return $this::successReturn([], 'User logged out successfully', ResponseAlias::HTTP_OK);
        }
        catch (\Exception $exception) {
            return $this::errorReturn([], 'Something went wrong', ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

//
//    public function resetPassword(Request $request): JsonResponse
//    {
//        if(filter_var($request->get('user'), FILTER_VALIDATE_EMAIL)){
//            $request->merge(['email' => $request->get('user')]);
//
//        }else if(is_numeric($request->get('user'))){
//            $request->merge(['phone_number' => $request->get('user')]);
//
//        }else{
//            return $this::errorReturn([], 'Please enter a valid email address or phone number', ResponseAlias::HTTP_BAD_REQUEST);
//        }
//        $validator = ValidationService::resetPasswordValidator(request()->all());
//        if ($validator->fails()) {
//            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
//        }
//
//        $identifier = $request->get('email') ?? $request->get('phone_number');
//        $reset_token = PasswordReset::validateToken($identifier, $request->reset_token);
//
//        if(!$reset_token->status) {
//            return $this::errorReturn([], $reset_token->message, ResponseAlias::HTTP_BAD_REQUEST);
//        }
//
//        $user = User::where(function($query) use ($request) {
//            $query->where('email', $request->email)
//                ->orWhere('phone_number', $request->phone_number);
//        })
//            ->where('is_active', true)
//            ->first();
//        $user->password = bcrypt(request()->password);
//        $user->save();
//        return $this::successReturn([], 'Password reset successfully', ResponseAlias::HTTP_OK);
//    }
//
//    public function resetPasswordAdmin(Request $request): JsonResponse
//    {
//        $validator = ValidationService::resetPasswordAdminValidator(request()->all());
//        if ($validator->fails()) {
//            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
//        }
//
//        $identifier = $request->get('email');
//        $reset_token = PasswordReset::validateToken($identifier, $request->reset_token);
//        if(!$reset_token->status) {
//            return $this::errorReturn([], $reset_token->message, ResponseAlias::HTTP_BAD_REQUEST);
//        }
//
//        $user = User::query()->where('email', $request->email)
//            ->where('is_active', true)
//            ->first();
//        $user->password = bcrypt(request()->password);
//        $user->save();
//        return $this::successReturn([], 'Password reset successfully', ResponseAlias::HTTP_OK);
//    }
}
