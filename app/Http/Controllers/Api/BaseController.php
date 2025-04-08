<?php

namespace App\Http\Controllers\API;

use App\Mail\GenericEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message, $code = 200)
    {
        $response = [
            'success' => true,
            'status' => $code,
            'data'    => $result,
            'message' => $message,
        ];

        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 400)
    {
        $response = [
            'success' => false,
            'status' => $code,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    public function sendPaginatedResponse($paginator, $message = 'Data fetched successfully', $code = 200)
{
    return response()->json([
        'success' => true,
        'status' => $code,
        'message' => $message,
        'data' => $paginator->items(),
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ]
    ], $code);
}


public function sendMail($to, $subject, $view, $data = [])
{
    try {
        Mail::to($to)->send(new GenericEmail($subject, $view, $data));

        // ✅ Return success response
        return $this->sendResponse(null, 'Email sent successfully', 200);

    } catch (\Exception $e) {
        // ❌ Return error response
        return $this->sendError('Mail Sending Failed', ['error' => $e->getMessage()], 500);
    }
}

}
