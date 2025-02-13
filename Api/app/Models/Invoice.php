<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    protected $table = 'invoices';
    public $timestamps = false;

    protected $casts = [
        'shop_id' => 'int',
        'route_id' => 'int',
        'agency_id' => 'int',
        'is_active' => 'bool',
    ];

    protected $fillable = [
        'shop_id',
        'route_id',
        'agency_id',
        'date',
        'amount',
        'invoice_no',
        'is_active',
        'remark',
        'loading_date',
        'loading_time',
        'loading_no'
    ];

    public static function getInvoiceList($all)
    {
        $query = Invoice::query()
            ->select('invoices.*','shops.shop_name', 'routes.route_name')
            ->where('invoices.agency_id', auth()->user()->agency_id)
            ->leftJoin('shops', 'invoices.shop_id', '=', 'shops.id')
            ->leftJoin('routes', 'invoices.route_id', '=', 'routes.id');


        if (!empty($all['keyword'])) {
            $query->where(function ($query) use ($all) {
                $query->where('invoices.invoice_no', 'LIKE', '%' . $all['keyword'] . '%');
                $query->orWhere('shops.shop_name', 'LIKE', '%' . $all['keyword'] . '%');
                $query->orWhere('routes.route_name', 'LIKE', '%' . $all['keyword'] . '%');
            });
        }

        if ($all['is_active'] === true || $all['is_active'] === false) {
            $query->where('invoices.is_active', $all['is_active']);
        }

        $totalCount = $query->count();

        $page_no = $all['page_no'];
        $page_size = $all['page_size'];
        $start_no = ($page_no - 1) * $page_size;

        $all_invoice_list = $query->offset($start_no)
            ->limit($page_size)
            ->get()->toArray();

        $route_list = Route::query()
            ->select('routes.id', 'routes.route_name')
            ->where('routes.agency_id', auth()->user()->agency_id)
            ->where('routes.is_active', 1)
            ->get()->toArray();

        $shop_list = Shop::query()
            ->select('shops.id', 'shops.shop_name')
            ->where('shops.agency_id', auth()->user()->agency_id)
            ->where('shops.is_active', 1)
            ->get()->toArray();

        return [
            'total_count' => $totalCount,
            'invoice_list' => $all_invoice_list,
            'shop_list' => $shop_list,
            'route_list' => $route_list,
        ];
    }

    public static function deleteInvoice($request)
    {
        Invoice::where('invoice_ref', $request['ref'])
            ->update([
                'is_active' => 0,
                'updated_user_id' => Auth::id(),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }

    public static function updateInvoice($request)
    {
        Invoice::where('invoice_ref', $request['invoice_ref'])
            ->update([
                'amount' => $request['amount'],
                'shop_id' => $request['shop_id'],
                'route_id' => $request['route_id'],
                'invoice_no' => $request['invoice_no'],
                'date' => $request['date'],
                'remark' => $request['remark'],
                'is_active' => $request['is_active'],
                'updated_user_id' => auth()->user()->id,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
