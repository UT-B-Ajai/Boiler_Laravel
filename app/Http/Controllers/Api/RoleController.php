<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController;

class RoleController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('perPage');
            $page = $request->input('page', 1);

            if ($perPage) {
                $roles = Role::with('permissions')
                    ->orderBy('id', 'desc')
                    ->paginate($perPage, ['*'], 'page', $page);

                return $this->sendPaginatedResponse($roles, 'Roles fetched with pagination');
            } else {
                $roles = Role::with('permissions')->orderBy('id', 'desc')->get();
                return $this->sendResponse($roles, 'Roles fetched without pagination');
            }
        } catch (\Exception $e) {
            return $this->sendError('Error fetching roles', ['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name',
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'sanctum',
            ]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return $this->sendResponse([
                'role' => $role,
                'permissions' => $role->permissions,
            ], 'Role and permissions created successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Error creating role', ['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $role = Role::with('permissions')->find($id);

            if (!$role) {
                return $this->sendError('Role not found', [], 404);
            }

            return $this->sendResponse($role, 'Role fetched successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error fetching role', ['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendError('Role not found', [], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:roles,name,' . $id,
                'permissions' => 'nullable|array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $role->update(['name' => $request->name]);

            if ($request->has('permissions')) {
                $role->syncPermissions($request->permissions);
            }

            return $this->sendResponse([
                'role' => $role,
                'permissions' => $role->permissions,
            ], 'Role updated successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error updating role', ['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $role = Role::find($id);

            if (!$role) {
                return $this->sendError('Role not found', [], 404);
            }

            $role->delete();

            return $this->sendResponse([], 'Role deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error deleting role', ['error' => $e->getMessage()], 500);
        }
    }
}
