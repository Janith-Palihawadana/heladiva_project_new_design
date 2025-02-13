<?php

namespace App\Http\Controllers;

use App\Models\SaleRef;
use App\Models\Vehicle;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SaleRefController extends Controller
{
    public function getSaleRefList(Request $request): JsonResponse
    {
        try {
            $user_roles = SaleRef::getSaleRefs($request->all());
            return $this->successReturn( $user_roles, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveSaleRef(Request $request): JsonResponse
    {
        $validator = ValidationService::saveSaleRefValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            SaleRef::saveSaleRef($request->all());
            return $this->successReturn( [], 'Data Saved Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to save data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateSaleRef(Request $request): JsonResponse
    {
        $validator = ValidationService::updateSaleRefValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            SaleRef::updateSaleRef($request->all());
            return $this->successReturn( [], 'Data Updated Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to update data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteSaleRef(Request $request): JsonResponse
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            SaleRef::deleteSaleRef($request->all());
            return $this->successReturn( [], 'Data Deleted Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to delete data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
