<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopAssignRoutes extends Model
{
    protected $table = 'shop_assign_routes';

    protected $casts = [
        'is_active' => 'bool',
        'shop_id' => 'int',
        'agency_id' => 'int'
    ];

    protected $fillable = [
        'shop_id',
        'route_id',
        'agency_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];
}
{

}
