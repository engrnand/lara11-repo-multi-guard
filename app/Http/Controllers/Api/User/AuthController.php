<?php

namespace App\Http\Controllers\Api\User;

use App\Enum\UserGaurdEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserLoginRequest;
use App\Http\Requests\User\UserRegisterRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{

    public function login(UserLoginRequest $request)
    {
        $payload = $request->validated();

        $auth = Auth::guard(UserGaurdEnum::USER->value);
        if ($auth->attempt($payload, $request->get('remember'))) {
            $user = auth()->guard(UserGaurdEnum::USER->value)->user();
            $token = $user->createToken(UserGaurdEnum::USER->value, ['guard:' . UserGaurdEnum::USER->value])->plainTextToken;
            return successResponse([
                "user" => $user,
                "access_token" => $token
            ], trans('auth.loggin'));
        } else {
            return errorResponse(trans('auth.failed'));
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return successResponse([], trans('auth.logout'));
    }


    protected function create(UserRegisterRequest $request)
    {
        $payload = $request->validated();

        try {
            $model = new User;
            DB::transaction(function () use (&$model, $payload, $request) {
                $model->toFill($payload, ['avatar']);
                $model->password = $payload['password'];
                //uploading avatar image
                if ($request->hasFile('avatar')) {
                    $model->avatar = uploadFile($request->avatar, filename: UserGaurdEnum::USER->value . $model->id);
                }
                $model->save();
            });
        } catch (Exception $e) {
            return errorResponse($e->getMessage(), 500);
        }

        return successResponse($model, trans('auth.user_created'));
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return successResponse($user, trans('auth.profile_fetch'));
    }
}
