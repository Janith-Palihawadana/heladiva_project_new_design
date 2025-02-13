<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function successReturn($data, $message = 'Data Return Successfully', $status = 200): JsonResponse
    {
        return response()->json(
            [
                'data' => $data,
                'message' => $message,
                'status' => $status
            ],
            $status
        );
    }

    public function errorReturn($data, $message = 'Data Return Unsuccessfully', $status = 400): JsonResponse
    {
        return response()->json(
            [
                'data' => $data,
                'message' => $message,
                'status' => $status
            ],
            $status
        );
    }
}
