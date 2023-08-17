<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Cache;

class AuthController extends BaseController
{
    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            if (!Auth::attempt($credentials)) {
                return $this->responseJsonError('Sorry, the informations you entered are incorrect. Please try again or sign up', null, 401);
            }

            $accessToken = Auth::user()->createToken('access_token')->plainTextToken;

            // Store the access token in cache for later use
            Cache::put('access_token', $accessToken, 60 * 24);

            return $this->respondWithToken($accessToken);
        } catch (\Exception $e) {
            return $this->responseJsonErrorInternalServerError('Something went wrong, please try again later', $e->getMessage());
        }
    }

     /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {

            $user = User::create($request->except('password') + [
                'password' => Hash::make($request->input('password')),
            ]);

            // $user->addRole('user');

            return $this->responseJsonSuccess('User created successfully');
        } catch (\Exception $ex) {
            return $this->responseJsonErrorInternalServerError('Something went wrong, please try again later', $ex->getMessage());
        }
    }


     /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            Auth::logout();
            Cache::delete('access_token');
            return $this->responseJsonSuccess('Successfully logged out');
        } catch (\Exception $e) {
            return $this->responseJsonErrorInternalServerError('Something went wrong, please try again later', $e->getMessage());
        }
    }



    /**
     * @param $token
     * @return JsonResponse
     */
    protected function respondWithToken($token): JsonResponse
    {
        return $this->responseJsonSuccess('Successfully logged in', [
            'access_token' => $token,
            'token_type' => 'bearer',
        ]);
    }
}
