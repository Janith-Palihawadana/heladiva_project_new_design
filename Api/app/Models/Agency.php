<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{

    protected $table = 'agencies';
    public $timestamps = false;

    protected $casts = [
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'name',
        'is_active'
    ];

    public static function getAgencyList(array $all)
    {
        return Agency::query()
            ->select('agencies.id', 'agencies.name')
            ->where('agencies.is_active', 1)
            ->get()->toArray();
    }
}
