<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;

class MailController extends BaseController
{
    public function sendCustomMail(Request $request)
    {
        $request->validate([
            'email'   => 'required|email',
            'subject' => 'required|string',
            'title'   => 'required|string',
            'message' => 'required|string',
        ]);

        $data = [
            'title' => $request->title,
            'message' => $request->message,
            'subject' => $request->subject
        ];

        $errorResponse = $this->sendMail(
            $request->email,
            $request->subject,
            'emails.generic',
            $data
        );

        if ($errorResponse) {
            return $errorResponse;
        }

        return $this->sendResponse(null, 'Email sent successfully');
    }
}
