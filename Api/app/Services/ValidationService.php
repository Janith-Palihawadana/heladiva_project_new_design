<?php

namespace App\Services;
use App\Models\Invoice;
use App\Models\Route;
use App\Models\SaleRef;
use App\Models\Shop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ValidationService
{
    public static function refValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'ref' => 'required|string'
        ]);
    }

    public static function registerValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'full_name' => 'required|string',
            'phone_number' => ['required', 'regex:/^\+94[0-9]{9}$/', 'unique:users'],
            'email' => 'required|unique:users|email',
            'address' => 'required|string',
            'city' => 'required|numeric',
            'password' => 'required|min:8|string',
            'confirm_password' => 'required|min:8|string|same:password',
            'language_ref' => 'required|string|exists:languages,language_ref',
            'otp_code' => 'required|numeric|digits:4'
        ]);
    }

    public static function loginValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'email' => 'sometimes|required_without:phone_number|email|exists:users',
            'phone_number' => ['sometimes', 'required_without:email', 'regex:/^\+94[0-9]{9}$/', 'exists:users'],
            'password' => 'required|min:8|string',
        ]);
    }

    public static function saveUserValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\+94[0-9]{9}$/', 'unique:users'],
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8|string',
            'confirm_password' => 'required|min:8|string|same:password',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function updateUserValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        $userID = User::where('user_ref', $request["user_ref"])->first()->id;
        return Validator::make($request, [
            'user_ref' => 'required|string|exists:users,user_ref',
            'name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\+94[0-9]{9}$/',
                Rule::unique('users', 'phone_number')->ignore($userID, 'id')],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userID, 'id')],
            'password' => 'nullable|min:8|string',
            'confirm_password' => 'nullable|min:8|string|same:password',
            'is_active' => 'required|boolean',
        ]);
    }


    public static function saveUserRoleValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'role_name' => 'required|string',
            'agency_id' => 'required|numeric|exists:agencies,id',
        ]);
    }

    public static function deleteUserRoleValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'role_ref' => 'required|string|exists:user_roles,role_ref',
        ]);
    }

    public static function UpdateUserRoleValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'role_name' => 'required|string',
            'role_ref' => 'required|string|exists:user_roles,role_ref',
        ]);
    }

    public static function updateUsersValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        $userID = User::where('user_ref', $request["user_ref"])->first()->id;

        return Validator::make($request, [
            'user_ref' => 'required|string|exists:users,user_ref',
            'full_name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\+94[0-9]{9}$/',
                Rule::unique('users', 'phone_number')->ignore($userID, 'id')],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userID, 'id')],
            'is_active' => 'required|boolean',
            'role_id' => 'required|numeric',
            'password' => 'nullable|min:8|string',
            'confirm_password' => 'nullable|min:8|string|same:password',
        ]);
    }

    public static function saveUsersValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'full_name' => 'required|string|max:255',
            'phone_number' => ['required', 'regex:/^\+94[0-9]{9}$/', 'unique:users'],
            'email' => 'required|unique:users|email',
            'is_active' => 'required|boolean',
            'role_id' => 'required|numeric',
            'agency_id' => 'required|numeric|exists:agencies,id',
            'password' => 'nullable|min:8|string',
            'confirm_password' => 'nullable|min:8|string|same:password',
        ]);
    }

    public static function deleteUserValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'user_ref' => 'required|string|exists:users,user_ref',
        ]);
    }

    public static function saveAreaValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'area_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function updateAreaValidator(array $all): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($all, [
            'area_ref' => 'required|string|exists:areas,area_ref',
            'area_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function deleteAreaValidator(array $all)
    {
        return Validator::make($all, [
            'area_ref' => 'required|string|exists:areas,area_ref',
        ]);
    }

    public static function saveShopValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'shop_name' => 'required|string|max:255|unique:shops,shop_name',
            'due_date_count' => 'required|numeric',
            'route_id.*.id' => 'required|numeric|exists:routes,id',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function updateShopValidator(array $all): \Illuminate\Contracts\Validation\Validator
    {
        $shop = Shop::where('shop_ref', $all["shop_ref"])->first();
        return Validator::make($all, [
            'shop_ref' => 'required|string|exists:shops,shop_ref',
            'shop_name' => ['required', 'string', 'max:255', Rule::unique('shops', 'shop_name')->ignore($shop->id)],
            'due_date_count' => 'required|numeric',
            'route_id.*.id' => 'required|numeric|exists:routes,id',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function deleteShopValidator(array $all)
    {
        return Validator::make($all, [
            'shop_ref' => 'required|string|exists:shops,shop_ref',
        ]);
    }

    public static function saveRouteValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'route_name' => 'required|string|max:255|unique:routes,route_name',
            'is_active' => 'required|boolean',
            'area_id.*.id' => 'required|numeric|exists:areas,id',
        ]);
    }

    public static function updateRouteValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        $route = Route::where('routes_ref', $request["route_ref"])->first();
        return Validator::make($request, [
            'route_name' => ['required', 'string', 'max:255', Rule::unique('routes', 'route_name')->ignore($route->id)],
            'is_active' => 'required|boolean',
            'area_id.*.id' => 'required|numeric|exists:areas,id',
            'route_ref' => 'required|string|exists:routes,routes_ref',
        ]);
    }

    public static function saveVehicleValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'vehicle_number' => 'required|string|max:255|unique:vehicles,vehicle_number',
            'area_id' => 'required|numeric|exists:areas,id',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function updateVehicleValidator(array $all): \Illuminate\Contracts\Validation\Validator
    {
        $vehicle = Vehicle::where('vehicle_ref', $all["vehicle_ref"])->first();
        return Validator::make($all, [
            'vehicle_ref' => 'required|string|exists:vehicles,vehicle_ref',
            'vehicle_number' => ['required', 'string', 'max:255', Rule::unique('vehicles', 'vehicle_number')->ignore($vehicle->id)],
            'area_id' => 'required|numeric|exists:areas,id',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function saveSaleRefValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'sale_ref_name' => 'required|string|max:255|unique:sale_refs,sale_ref_name',
            'area_id' => 'required|numeric|exists:areas,id',
            'is_active' => 'required|boolean',
        ]);
    }

    public static function updateSaleRefValidator(array $all): \Illuminate\Contracts\Validation\Validator
    {
        $saleRef = SaleRef::where('sale_ref_ref', $all["sale_ref_ref"])->first();
        return Validator::make($all, [
            'sale_ref_ref' => 'required|string|exists:sale_refs,sale_ref_ref',
            'sale_ref_name' => ['required', 'string', 'max:255', Rule::unique('sale_refs', 'sale_ref_name')->ignore($saleRef->id)],
            'area_id' => 'required|numeric|exists:areas,id',
            'is_active' => 'required|boolean',
        ]);
    }


    public static function saveInvoiceValidator($request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request, [
            'route_id.id' => 'required|numeric|exists:routes,id',
        ]);
    }

    public static function updateInvoiceValidator(array $all): \Illuminate\Contracts\Validation\Validator
    {
        $invoiceRef = Invoice::where('invoice_ref', $all["invoice_ref"])->first();
        return Validator::make($all, [
            'invoice_ref' => 'required|string|exists:invoices,invoice_ref',
            'invoice_no' => ['required', 'string', 'max:255', Rule::unique('invoices', 'invoice_no')->ignore($invoiceRef->id)],
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'shop_id' => 'required|numeric|exists:shops,id',
            'is_active' => 'required|boolean',
        ]);

    }
}
