<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteAssignAreas;
use App\Models\Shop;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RouteController extends Controller
{

    public function getRoutesList(Request $request)
    {
        try {
            $shop_list = Route::getRouteList($request->all());
            return $this->successReturn($shop_list, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveRoute(Request $request)
    {
        $validator = ValidationService::saveRouteValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $route_id = Route::saveRoute($request->all());
            foreach ($request->area_id as $area_id) {
                Route::saveRouteArea($route_id, $area_id);
            }
            return $this->successReturn([], 'New Route added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Route create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function getRouteDetails(Request $request): JsonResponse
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            $route = Route::where('routes_ref', $request->ref)->first();
            if (!$route) {
                return $this::errorReturn([], 'Route not found', ResponseAlias::HTTP_BAD_REQUEST);
            }
            $routeData = new \stdClass();
            $routeData->routeDetails = Route::getRouteDetails($request->all());
            $routeData->areas = Route::routeAreas($route->id);
            return $this->successReturn($routeData, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateRoutes(Request $request): JsonResponse
    {
        $validator = ValidationService::updateRouteValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Route::updateRoute($request->all());
            $route_id = Route::where('routes_ref', $request['route_ref'])->first()->id;
            RouteAssignAreas::where('route_id', $route_id)->update(['is_active' => 0]);

            foreach ($request->area_id as $area_id) {
                Route::saveRouteArea($route_id, $area_id);
            }
            return $this->successReturn([], 'Data Updated Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteRoutes(Request $request){
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Route::deleteRoute($request->all());
            return $this->successReturn([], 'Data Deleted Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

}
