<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;


class UserAuthController extends Controller
{
    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->all();
        $data['password'] = bcrypt($request->input('password'));

        $user = User::create($data);

        return response()->json([
            'success' => 'true',
            'data' => ['user' => $user, 'token' => $this->createToken($user)]
        ]);
    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->all();

        if (!auth()->attempt($data)) {
            return response()->json([
                'success' => 'false',
                'data' => ['error_message' => 'Kullanıcı Adı veya Şifre yanlış.']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => ['token' => $this->createToken(auth()->user())]
        ]);
    }

    /**
     * @param User $user
     * @return string
     */
    private function createToken(User $user): string
    {
        return $user->createToken('API Token')->accessToken;
    }
}
