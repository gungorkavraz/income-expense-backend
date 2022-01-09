<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Request;


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
            'success' => true,
            'user' => $user,
            'token' => $this->createToken($user),
            'message' => 'Kullanıcı kaydı başarıyla gerçekleşti.'
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
                'success' => false,
                'error_message' => 'Kullanıcı Adı veya Şifre yanlış.'
            ]);
        }
        error_log(auth()->user());
        return response()->json([
            'success' => true,
            'user' => auth()->user(),
            'token' => $this->createToken(auth()->user())
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuthenticatedUser(Request $request): JsonResponse
    {
        error_log(auth('api')->user());
        if (auth('api')->user()) {
            return response()->json([
                'success' => true,
                'user' => auth('api')->user()
            ]);
        } else {
            return response()->json([
                'success' => false,
            ]);
        }

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
