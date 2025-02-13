<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SaleRef extends Model
{
    protected $table = 'sale_refs';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
        'agency_id' => 'int',
        'area_id' => 'int'
    ];

    protected $fillable = [
        'sale_ref_name',
        'agency_id',
        'area_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];

    public static function getSaleRefs($all)
    {
        $query = SaleRef::query()
            ->select('sale_refs.*')
            ->where('sale_refs.agency_id', auth()->user()->agency_id);

        if (!empty($all['keyword'])) {
            $query->where(function ($query) use ($all) {
                $query->where('sale_refs.sale_ref_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $query->where('sale_refs.is_active', $all['is_active']);
        }

        $totalCount = $query->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $sale_refs_list = $query->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $area_list = Areas::query()
            ->select('areas.id', 'areas.name')
            ->where('areas.agency_id', auth()->user()->agency_id)
            ->where('areas.is_active', 1)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'sale_ref_list' => $sale_refs_list,
            'area_list' => $area_list
        ];
    }

    public static function saveSaleRef($request)
    {
        SaleRef::create([
            'sale_ref_name' => $request['sale_ref_name'],
            'agency_id' => auth()->user()->agency_id,
            'area_id'=> $request['area_id'],
            'is_active' => $request['is_active'],
            'created_user_id' => auth()->user()->id,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function updateSaleRef($request)
    {
        SaleRef::where('sale_ref_ref', $request['sale_ref_ref'])
            ->update([
                'sale_ref_name' => $request['sale_ref_name'],
                'is_active' => $request['is_active'],
                'area_id'=> $request['area_id'],
                'updated_user_id' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function deleteSaleRef($request)
    {
        SaleRef::where('sale_ref_ref', $request['ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
