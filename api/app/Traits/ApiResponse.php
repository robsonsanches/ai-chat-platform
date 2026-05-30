<?php

namespace App\Traits;

trait ApiResponse
{
    protected function success($data = null, string $message = 'Success', int $statusCode = 200, string $code = 'SUCCESS')
    {
        $response = [
            'status' => 'success',
            'code' => $code,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function error(string $message = 'Error', int $statusCode = 400, $errors = null, string $code = 'ERROR')
    {
        $response = [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];

        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}