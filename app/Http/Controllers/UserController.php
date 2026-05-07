<?php
namespace App\Http\Controllers;

use App\Http\Requests\User\UserStoreRequest;
use App\Http\Requests\User\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

#[Group('Ползователь')]
class UserController extends Controller
{
    /**
     * Регистрация
     *
     * @unauthenticated
     */
    public function register(UserStoreRequest $request)
    {
        $data             = $request->validated();
        $data['password'] = Hash::make($data['password']);

        DB::beginTransaction();
        try {
            $user  = User::create($data);
            $token = $user->createToken('access-token')->plainTextToken;

            DB::commit();

            return $this->success([
                'user'  => new UserResource($user),
                'token' => $token,
            ]);
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Данные юзера
     */
    public function me(Request $request)
    {
        return $this->success(new UserResource($request->user()));
    }

    /**
     * Обновление данные юзера
     */
    public function update(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $data = array_filter($data, fn($value) => ! empty($value));

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        DB::beginTransaction();

        try {
            $request->user()->update($data);

            DB::commit();

            return $this->success(message: 'Данные успешно обновлены');
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }

    /**
     * Удалить аккаунт
     */
    public function delete(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->user()->delete();

            DB::commit();

            return $this->success(message: 'Аккаунт успешно удален');
        } catch (Throwable $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), Response::HTTP_BAD_GATEWAY);
        }
    }
}
