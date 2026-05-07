<?php
namespace App\Http\Controllers;

use Illuminate\Http\Resources\Json\JsonResource;

abstract class Controller
{
    public function success(JsonResource | array $data = [], string $message = '', int $status = 200)
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }

    public function error(string $message = '', int $status = 400, JsonResource | array $data = [])
    {
        return response()->json([
            'success' => false,
            'data'    => $data,
            'message' => $message,
        ], $status);
    }
}
