<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Shop extends Model
{

    protected $table = 'shops';

    protected $casts = [
        'is_active' => 'bool',
        'agency_id' => 'int',
        'area_id' => 'int'
    ];

    protected $fillable = [
        'shop_name',
        'due_date_count',
        'agency_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];

    public static function getShopList($all)
    {
        $query = Shop::query()
            ->select('shops.*')
            ->where('shops.agency_id', auth()->user()->agency_id);

        if (!empty($all['keyword'])) {
            $query->where(function ($query) use ($all) {
                $query->where('shops.shop_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $query->where('shops.is_active', $all['is_active']);
        }

        $totalCount = $query->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $all_shop_list = $query->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $route_list = Route::query()
            ->select('routes.id', 'routes.route_name')
            ->where('routes.agency_id', auth()->user()->agency_id)
            ->where('routes.is_active', 1)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'shop_list' => $all_shop_list,
            'route_list' => $route_list
        ];
    }

    public static function saveShop($request)
    {
        $shop = Shop::create([
            'shop_name'=>$request['shop_name'],
            'is_active' => $request['is_active'],
            'due_date_count' => $request['due_date_count'],
            'agency_id' => auth()->user()->agency_id,
            'created_user_id' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        return $shop->id;
    }

    public static function updateShop($request)
    {
        Shop::where('shop_ref', $request['shop_ref'])
            ->update([
                'shop_name'=>$request['shop_name'],
                'is_active' => $request['is_active'],
                'due_date_count' => $request['due_date_count'],
                'agency_id' => auth()->user()->agency_id,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function deleteShop($request)
    {
        Shop::where('shop_ref', $request['shop_ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function saveShopAssignRoutes($route_id, $shop_id){

        ShopAssignRoutes::create([
            'shop_id' => $shop_id,
            'route_id' => $route_id,
            'is_active' => 1,
            'agency_id' => auth()->user()->agency_id,
            'created_user_id' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public static function getShopDetails($all){
        return Shop::select('shop_name','is_active','due_date_count')
            ->where('shop_ref', $all['ref'])->first();
    }

    public static function shopRoutes($shop_id){
        return ShopAssignRoutes::select('routes.id','routes.route_name')
            ->leftjoin('routes', 'routes.id', '=', 'shop_assign_routes.route_id')
            ->where('shop_assign_routes.shop_id', $shop_id)
            ->where('shop_assign_routes.is_active', 1)
            ->get()->toArray();
    }
}
