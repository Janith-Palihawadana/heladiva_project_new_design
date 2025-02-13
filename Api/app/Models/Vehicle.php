<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Vehicle extends Model
{
    protected $table = 'vehicles';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
        'agency_id' => 'int',
        'area_id' => 'int'
    ];

    protected $fillable = [
        'vehicle_number',
        'agency_id',
        'area_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];


    public static function getVehicles($all)
    {
        $query = Vehicle::query()
            ->select('vehicles.*')
            ->where('vehicles.agency_id', auth()->user()->agency_id);

        if (!empty($all['keyword'])) {
            $query->where(function ($query) use ($all) {
                $query->where('vehicles.vehicle_number', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $query->where('vehicles.is_active', $all['is_active']);
        }

        $totalCount = $query->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $all_shop_list = $query->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $area_list = Areas::query()
            ->select('areas.id', 'areas.name')
            ->where('areas.agency_id', auth()->user()->agency_id)
            ->where('areas.is_active', 1)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'shop_list' => $all_shop_list,
            'area_list' => $area_list
        ];
    }

    public static function saveVehicle($request)
    {
        Vehicle::create([
            'vehicle_number' => $request['vehicle_number'],
            'agency_id' => auth()->user()->agency_id,
            'area_id'=> $request['area_id'],
            'is_active' => $request['is_active'],
            'created_user_id' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function updateVehicle($request)
    {
        Vehicle::where('vehicle_ref', $request['vehicle_ref'])
            ->update([
                'vehicle_number' => $request['vehicle_number'],
                'is_active' => $request['is_active'],
                'area_id'=> $request['area_id'],
                'updated_user_id' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function deleteVehicle($request)
    {
        Vehicle::where('vehicle_ref', $request['ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
