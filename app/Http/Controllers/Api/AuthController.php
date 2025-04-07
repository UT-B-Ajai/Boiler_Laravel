<?php

namespace App\Http\Controllers\API;

use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Http\Controllers\API\BaseController as BaseController;

class AuthController extends BaseController
{

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name'       => 'required|string|max:255',
                'last_name'        => 'nullable|string|max:255',
                'email'      => 'required|email|unique:users,email',
                'password'   => 'required|min:6',
                'c_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = User::create([
                'first_name'     => $request->first_name,
                'last_name'      => $request->last_name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Optional: Generate token if you're using Sanctum or Passport
            $token = $user->createToken('MyAppToken')->plainTextToken;

            $response = [
                'user'  => $user,
                'token' => $token,
            ];

            return $this->sendResponse($response, 'User registered successfully');
        } catch (Exception $e) {
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }


    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $success = [
                    'token' => $user->createToken('MyApp')->plainTextToken,
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ];

                return $this->sendResponse($success, 'User login successfully.');
            } else {
                return $this->sendError('Email or password is incorrect.', ['error' => 'Invalid credentials'], 401);
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Forgot Password API
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return $this->sendResponse([], 'Password reset link sent to your email.');
            }

            return $this->sendError('Failed to send reset link.', [], 400);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset Password API
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422);
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password),
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return $this->sendResponse([], 'Password has been reset successfully.');
            }

            return $this->sendError('Invalid token or email.', [], 400);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', ['error' => $e->getMessage()], 500);
        }
    }
}
