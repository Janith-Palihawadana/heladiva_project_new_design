<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{

    protected $table = 'permissions';
    public $timestamps = true;

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'permission_name',
        'permission_code',
        'is_active'
    ];

}
