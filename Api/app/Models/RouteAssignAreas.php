<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteAssignAreas extends Model
{
    protected $table = 'route_assign_areas';

    protected $casts = [
        'is_active' => 'bool',
        'route_id' => 'int',
        'agency_id' => 'int',
        'area_id' => 'int'
    ];

    protected $fillable = [
        'route_id',
        'area_id',
        'agency_id',
        'is_active',
        'created_at',
        'updated_at',
        'created_user_id',
        'updated_user_id'
    ];

}
