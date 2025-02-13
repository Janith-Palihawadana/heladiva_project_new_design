<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Services\ValidationFormatService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class VehicleController extends Controller
{
    public function getVehiclesList(Request $request): JsonResponse
    {
        try {
            $user_roles = Vehicle::getVehicles($request->all());
            return $this->successReturn( $user_roles, 'Data Returned Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function saveVehicle(Request $request)
    {
        $validator = ValidationService::saveVehicleValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Vehicle::saveVehicle($request->all());
            return $this->successReturn([], 'New Vehicle added successfully', ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Vehicle create failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function updateVehicle(Request $request)
    {
        $validator = ValidationService::updateVehicleValidator($request->all());
        if ($validator->fails()) {
            return $this->errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Vehicle::updateVehicle($request->all());
            return $this->successReturn([], 'Vehicle updated successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn([], 'Vehicle update failed.', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }

    public function deleteVehicle(Request $request)
    {
        $validator = ValidationService::refValidator($request->all());
        if ($validator->fails()) {
            return $this::errorReturn([], ValidationFormatService::formatErrors($validator->errors()), ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            Vehicle::deleteVehicle($request->all());
            return $this->successReturn([], 'Data Deleted Successfully', ResponseAlias::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->errorReturn( [], 'Failed to return data', ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
