<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PermissionController extends BaseController
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('perPage');
            $page = $request->input('page', 1);

            if ($perPage) {
                $permissions = Permission::orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
                return $this->sendPaginatedResponse($permissions, 'Permissions fetched successfully');
            } else {
                $permissions = Permission::orderBy('id', 'desc')->get();
                return $this->sendResponse($permissions, 'Permissions fetched successfully');
            }
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch permissions.', [$e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'sanctum',
            ]);

            return $this->sendResponse($permission, 'Permission created successfully');
        } catch (Exception $e) {
            return $this->sendError('Failed to create permission.', [$e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            return $this->sendResponse($permission, 'Permission details retrieved');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Permission not found', [], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:permissions,name,' . $id,
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $permission = Permission::findOrFail($id);
            $permission->update([
                'name' => $request->name
            ]);

            return $this->sendResponse($permission, 'Permission updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Permission not found', [], 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to update permission.', [$e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            $permission->delete();

            return $this->sendResponse([], 'Permission deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Permission not found', [], 404);
        } catch (Exception $e) {
            return $this->sendError('Failed to delete permission.', [$e->getMessage()]);
        }
    }

    public function assignPermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $role = Role::findOrFail($request->role_id);
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);

            return $this->sendResponse([
                'role_id' => $role->id,
                'assigned_permissions' => $role->permissions
            ], 'Permissions assigned successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to assign permissions.', [$e->getMessage()]);
        }
    }

    public function revokePermissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|integer|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        try {
            $role = Role::findOrFail($request->role_id);
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->revokePermissionTo($permissions);

            return $this->sendResponse([
                'role_id' => $role->id,
                'remaining_permissions' => $role->permissions
            ], 'Permissions revoked successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to revoke permissions.', [$e->getMessage()]);
        }
    }
}
