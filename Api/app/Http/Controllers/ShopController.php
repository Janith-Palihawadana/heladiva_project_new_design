<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use App\Models\Route;
use App\Models\Shop;
use App\Models\ShopAssignRoutes;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ShopController extends Controller
{
    public function getShopsList(Request $request)
    {
        try {
            $shop_list = Shop::getShopList($request->all());
            return $this->successReturn($shop_list, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }


    public function saveShop(Request $request)
    {
        $validator = ValidationService::saveShopValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $shop_id = Shop::saveShop($request->all());

            foreach ($request->route_id as $route) {
                Shop::saveShopAssignRoutes($route['id'],$shop_id);
            }
            return $this->successReturn([], 'New Shop added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Shop create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateShop(Request $request)
    {
        $validator = ValidationService::updateShopValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $shopId = Shop::where('shop_ref',$request->shop_ref)->first()->id;
            Shop::updateShop($request->all());
            ShopAssignRoutes::where('shop_id',$shopId)->update(['is_active'=> 0]);
            foreach ($request->route_id as $route) {
                Shop::saveShopAssignRoutes($route['id'],$shopId);
            }
            return $this->successReturn([], 'Shop updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Shop update failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteShop(Request $request)
    {
    $validator = ValidationService::deleteShopValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Shop::deleteShop($request->all());
            return $this->successReturn([], 'Shop deleted successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Shop delete failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getShopDetails(Request $request){
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $shop = Shop::where('shop_ref', $request->ref)->first();
            if (!$shop) {
                return $this::errorReturn([], 'Shop not found', ResponseAlias::HTTP_BAD_REQUEST);
            }
            $shopData = new \stdClass();
            $shopData->shopDetails = Shop::getShopDetails($request->all());
            $shopData->routes = Shop::shopRoutes($shop->id);
            return $this->successReturn($shopData, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Data Returned Unsuccessfully.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
