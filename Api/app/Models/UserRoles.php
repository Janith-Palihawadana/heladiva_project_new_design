<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserRoles extends Model
{
    protected $table = 'user_roles';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
        'agency_id' => 'int',
        'created_user_id' => 'int',
        'updated_user_id' => 'int',
    ];

    protected $fillable = [
        'role_name',
        'is_active',
        'agency_id',
        'created_user_id',
        'updated_user_id',
    ];


    public static function getUserRoles(array $all): array
    {
        $userRoles = UserRoles::query()
            ->select('user_roles.*')
            ->where('user_roles.is_active', 1)
            ->where('user_roles.agency_id', $all['agency_id']);

        if (!empty($all['keyword'])) {
            $userRoles->where(function ($query) use ($all) {
                $query->where('user_roles.role_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        $totalCount = $userRoles->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $userRoles = $userRoles->orderBy('user_roles.id', 'asc')
            ->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $permission_list = Permission::query()
            ->select('permissions.*')
            ->where('permissions.is_active', 1)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'userRole' => $userRoles,
            'permission_list' => $permission_list
        ];
    }

    public static function saveUserRoles($request)
    {
        $userRole = UserRoles::create([
            'role_name'=>$request['role_name'],
            'is_active' => true,
            'agency_id' => $request['agency_id'],
            'created_by' => Auth::id(),
        ]);
        $userRolID = $userRole->id;
        return $userRolID;
    }
}
