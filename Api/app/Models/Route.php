<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Route extends Model
{

    protected $table = 'routes';

    protected $casts = [
        'agency_id' => 'int',
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'route_name',
        'area_id',
        'agency_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];

    public static function getRouteList($all)
    {
        $query = Route::query()
            ->select('routes.*')
            ->where('routes.agency_id', auth()->user()->agency_id);

        if (!empty($all['keyword'])) {
            $query->where(function ($query) use ($all) {
                $query->where('routes.route_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $query->where('routes.is_active', $all['is_active']);
        }

        $totalCount = $query->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $all_route_list = $query->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $area_list = Areas::query()
            ->select('areas.id', 'areas.name')
            ->where('areas.agency_id', auth()->user()->agency_id)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'route_list' => $all_route_list,
            'area_list' => $area_list
        ];
    }

    public static function saveRoute($request)
    {
        $route = Route::create([
            'route_name' => $request['route_name'],
            'agency_id' => auth()->user()->agency_id,
            'is_active' => $request['is_active'],
            'created_user_id' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $route->id;
    }

    public static function saveRouteArea($route_id, $area_id)
    {
        RouteAssignAreas::create([
            'route_id' => $route_id,
            'area_id' => $area_id['id'],
            'is_active' => 1,
            'created_user_id' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function updateRoute($request)
    {
        Route::where('routes_ref', $request['route_ref'])
            ->update([
                'route_name' => $request['route_name'],
                'is_active' => $request['is_active'],
                'updated_user_id' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function deleteRoute($request)
    {
        Route::where('routes_ref', $request['ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function getRouteDetails($all){
        return Route::select('route_name','is_active')
            ->where('routes_ref', $all['ref'])->first();
    }

    public static function routeAreas($route_id){
        return RouteAssignAreas::select('areas.id','areas.name')
            ->leftjoin('areas', 'areas.id', '=', 'route_assign_areas.area_id')
            ->where('route_assign_areas.route_id', $route_id)
            ->where('route_assign_areas.is_active', 1)
            ->get()->toArray();
    }
}
