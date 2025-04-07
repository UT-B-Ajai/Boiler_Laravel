<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('perPage');
            $page = $request->input('page', 1);

            if ($perPage) {
                $users = User::with('roles:id,name')
                    ->orderBy('id', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                $users->getCollection()->transform(function ($user) {
                    $role = $user->roles->first();
                    $user->role_id = $role?->id;
                    $user->role_name = $role?->name;
                    unset($user->roles);
                    return $user;
                });

                return $this->sendPaginatedResponse($users, 'Users fetched with pagination');
            } else {
                $users = User::with('roles:id,name')->orderBy('id', 'desc')->get();

                $users->transform(function ($user) {
                    $role = $user->roles->first();
                    $user->role_id = $role?->id;
                    $user->role_name = $role?->name;
                    unset($user->roles);
                    return $user;
                });

                return $this->sendResponse($users, 'Users fetched without pagination');
            }
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:100',
                'last_name' => 'nullable|string|max:100',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'role_id' => 'nullable|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'password'   => Hash::make($request->password),
                'role_id'    => $request->role_id,
            ]);

            if ($request->role_id) {
                $role = Role::findById($request->role_id);
                $user->assignRole($role);
            }

            return $this->sendResponse($user->load('roles'), 'User created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with('roles')->findOrFail($id);
            return $this->sendResponse($user, 'User fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('User not found or error occurred', ['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|required|string|max:100',
                'last_name'  => 'nullable|string|max:100',
                'email'      => 'sometimes|required|email|unique:users,email,' . $id,
                'password'   => 'nullable|string|min:6',
                'role_id'    => 'nullable|exists:roles,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user->update([
                'first_name' => $request->first_name ?? $user->first_name,
                'last_name'  => $request->last_name ?? $user->last_name,
                'email'      => $request->email ?? $user->email,
                'password'   => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            if ($request->role_id) {
                $role = Role::findById($request->role_id);
                $user->syncRoles([$role]);
            }

            return $this->sendResponse($user->load('roles'), 'User updated successfully', 200);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return $this->sendError('User not found', [], 404);
            }

            $user->delete();

            return $this->sendResponse([], 'User deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong', ['error' => $e->getMessage()], 500);
        }
    }
}
