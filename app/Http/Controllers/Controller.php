<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function sendApiResponse($status = 'success', $data = [], $message = '', $code = 200) {
        $response = [
            'status' => $status,
            'data' => $data,
            'message' => $message
        ];

        return response()->json($response, $code);

    }
}
