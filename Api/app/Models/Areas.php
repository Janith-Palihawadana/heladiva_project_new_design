<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Areas extends Model
{

    protected $table = 'areas';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
        'agency_id' => 'int'
    ];

    protected $fillable = [
        'name',
        'agency_id',
        'is_active'
    ];


    public static function getAreaList(array $all)
    {
        $area_list = Areas::query()
            ->select('areas.*')
            ->where('areas.agency_id', auth()->user()->agency_id);

        if (!empty($all['keyword'])) {
            $area_list->where(function ($query) use ($all) {
                $query->where('areas.name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $area_list->where('areas.is_active', $all['is_active']);
        }

        $totalCount = $area_list->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $all_area_list = $area_list->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'area_list' => $all_area_list,
        ];
    }

    public static function saveArea($request)
    {
        Areas::create([
            'name'=>$request['area_name'],
            'is_active' => $request['is_active'],
            'created_user_id' => Auth::id(),
            'agency_id' => Auth::user()->agency_id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function updateArea($request)
    {
        Areas::where('area_ref', $request['area_ref'])
            ->update([
                'name'=>$request['area_name'],
                'is_active' => $request['is_active'],
                'updated_user_id' => Auth::id(),
                'agency_id' => Auth::user()->agency_id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function deleteArea($request)
    {
        Areas::where('area_ref', $request['area_ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
