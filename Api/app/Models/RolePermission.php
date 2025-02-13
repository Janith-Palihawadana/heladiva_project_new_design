<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{

    protected $table = 'role_permissions';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
        'role_id' => 'int',
        'permission_id' => 'int',
        'agency_id' => 'int',
    ];

    protected $fillable = [
        'permission_id',
        'role_id',
        'is_active',
        'value',
        'agency_id'
    ];

    public static function getRolePermission(array $all): array
    {
        $permission_list = RolePermission::query()
            ->select('role_permissions.value','role_permissions.is_active','role_permissions.role_id', 'permissions.permission_name', 'permissions.permission_code', 'permissions.id')
            ->leftJoin('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.is_active', 1)
            ->where('role_permissions.role_id', $all['role_id'])
            ->where('role_permissions.agency_id', auth()->user()->agency_id)
            ->get()->toArray();
        return $permission_list;
    }

    public static function saveRolePermission($all, $role_id)
    {
        $permission_list = $all['permission'];

        RolePermission::where('role_id', $role_id)->delete();

        foreach ($permission_list as $permission) {
            RolePermission::create([
                'role_id' => $role_id,
                'permission_id' => $permission['permission_id'],
                'agency_id'=> $all['agency_id'],
                'is_active' => 1,
                'value' => $permission['value']
            ]);
        }
    }

    public static function removeRolePermission($role_id)
    {
        RolePermission::where('role_id', $role_id)->delete();
    }

    public static function getUserPermissions($role_id,$agency_id)
    {
        $permission_list = RolePermission::query()
            ->select('permission_id')
            ->where('role_permissions.is_active', 1)
            ->where('role_permissions.role_id', $role_id)
            ->where('role_permissions.agency_id', $agency_id)
            ->where('role_permissions.value', '=', 1)
            ->get()
            ->pluck('permission_id');

        return $permission_list;
    }


}
