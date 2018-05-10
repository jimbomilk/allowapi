<?php

namespace App\Api\V1\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\JWTAuth;
use App\Http\Controllers\Controller;
use App\Api\V1\Requests\LoginRequest;
use Tymon\JWTAuth\Exceptions\JWTException;


class LoginController extends Controller
{
    /**
     * Log the user in
     *
     * @param LoginRequest $request
     * @param JWTAuth $JWTAuth
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request, JWTAuth $JWTAuth)
    {
        $credentials = $request->only(['email','password']);

        try {
            $token = Auth::attempt($credentials);

            if(!$token) {
                // Buscamos el user
                $user = User::where('email',$request->get('email'))->first();
                if (!$user){
                    $user = new User($request->all());
                    if(!$user->save()) {
                        throw new HttpException(500);
                    }else{
                        $token = Auth::attempt($credentials);
                        return response()
                            ->json([
                                'status' => 'ok',
                                'token' => $token,
                                'expires_in' => Auth::guard()->factory()->getTTL() * 60
                            ]);
                    }
                }else {

                    throw new HttpException(500);
                }
            }

        } catch (JWTException $e) {
            throw new HttpException(500);
        }

        return response()
            ->json([
                'status' => 'ok',
                'token' => $token,
                'expires_in' => Auth::guard()->factory()->getTTL() * 60
            ]);
    }
}
