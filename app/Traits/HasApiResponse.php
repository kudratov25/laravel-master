<?php

namespace App\Traits;

trait HasApiResponse
{
    protected function successResponse(mixed $data = null, string $message = 'Operation successful', int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function successIndexResponse(mixed $data = null, string $message = 'Operation successful', int $status = 200, array $pagination = []): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'     => 'success',
            'message'    => $message,
            'data'       => $data,
            'pagination' => $pagination,
        ], $status);
    }

    protected function errorResponse(string $message = 'Error', int $status = 500, mixed $errors = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }
}
