<?php
namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

#[Group('Авторизация')]
class AuthController extends Controller
{
    /**
     * Логин
     * 
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        if(!Auth::attempt($request->validated())){
            return $this->error('Неверный адрес электронной почты или пароль.', status: Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request->email)->first();
        $token = $user->createToken('access-token')->plainTextToken;

        return $this->success([
            'token' => $token
        ]);
    }

    /**
     * Выход из аккаунта
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(message: 'Выход из системы пройден успешно.');
    }
}
