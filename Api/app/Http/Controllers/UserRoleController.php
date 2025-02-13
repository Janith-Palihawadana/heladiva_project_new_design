<?php

namespace App\Http\Controllers;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRoles;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class UserRoleController extends Controller
{
    public function getUserRole(Request $request): JsonResponse
    {
        try {
            $user_roles = UserRoles::getUserRoles($request->all());
            return $this->successReturn( $user_roles, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveUserRole(Request $request)
    {
        $validator = ValidationService::saveUserRoleValidator($request->all());

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $role_id = UserRoles::saveUserRoles($request->all());
            RolePermission::saveRolePermission($request->all(),$role_id);
            return $this->successReturn([], 'New User Role added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'User Role create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteUseRole(Request $request)
    {
        $validator = ValidationService::deleteUserRoleValidator($request->all());

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $role = UserRoles::where('role_ref',$request->role_ref)->first();
            $role->is_active = 0;
            $role->save();

            return $this->successReturn([], 'Delete Role successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Delete Role failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateUsers(Request $request)
    {
        $validator = ValidationService::UpdateUserRoleValidator($request->all());

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $role = UserRoles::where('role_ref',$request->role_ref)->first();
            $role->role_name = $request->role_name;
            $role->save();

            RolePermission::removeRolePermission($role->id);
            RolePermission::saveRolePermission($request->all(),$role->id);

            return $this->successReturn([], 'New User Role added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'User Role create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getUsers(Request $request){
        {
            try {
                $users = User::getUsers($request->all(),false);
                $users_count = User::getUsers($request->all(),true);
                $user_roles = UserRoles::select('role_name','id')->where('is_active',1)->get();
                return $this->successReturn([
                    'users'=>$users,
                    'user_count'=>$users_count,
                    'user_roles' =>$user_roles
                ], 'Data Returned Successfully', ResponseAlias::HTTP_OK);

            } catch (\Exception $e) {
                Log::error($e);
                return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
            }
        }
    }

    public function saveUser(Request $request)
    {
        $validator = ValidationService::saveUsersValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            User::saveUser($request->all());
            return $this->successReturn([], 'User added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'User create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateUser(Request $request)
    {

        $validator = ValidationService::updateUsersValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            User::UpdateUser($request->all());
            return $this->successReturn([], 'User updated successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'User updated failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteUse(Request $request)
    {
        $validator = ValidationService::deleteUserValidator($request->all());

        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $user = User::where('user_ref',$request->user_ref)->first();
            $user->is_active = 0;
            $user->save();

            return $this->successReturn([], 'Delete User successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Delete User failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getRolePermission(Request $request): JsonResponse
    {
        try {
            $role_permissions = RolePermission::getRolePermission($request->all());
            $data = [
                'role_permissions' => $role_permissions
            ];
            return $this->successReturn( $data, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
