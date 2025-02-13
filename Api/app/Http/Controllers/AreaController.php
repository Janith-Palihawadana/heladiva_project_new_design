<?php

namespace App\Http\Controllers;

use App\Models\Areas;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AreaController extends Controller
{
    public function getAreasList(Request $request): JsonResponse
    {
        try {
            $area_list = Areas::getAreaList($request->all());
            return $this->successReturn( $area_list, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveArea(Request $request)
    {
        $validator = ValidationService::saveAreaValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Areas::saveArea($request->all());
            return $this->successReturn([], 'New Area added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Area create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateArea(Request $request)
    {
        $validator = ValidationService::updateAreaValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Areas::updateArea($request->all());
            return $this->successReturn([], 'Area updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Area update failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteArea(Request $request)
    {
        $validator = ValidationService::deleteAreaValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Areas::deleteArea($request->all());
            return $this->successReturn([], 'Area deleted successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Area delete failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

}
