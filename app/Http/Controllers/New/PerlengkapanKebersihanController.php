<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\PerlengkapanKebersihan;
use Illuminate\Http\Request;

class PerlengkapanKebersihanController extends Controller
{
    public function get_data_perlengkapan(Request $request)
    {
        $value_per_page = $request->query('value_per_page', 10);
        $name_query = $request->query('name');
        $has_stock = $request->query('has_stock');

        $query = PerlengkapanKebersihan::query();

        if ($name_query) {
            $query->where('name', 'like', '%' . $name_query . '%');
        }

        if ($has_stock === '1' || $has_stock === 'true' || !$request->has('include_zero_stock')) {
            $query->where('stock', '>', 0);
        }

        $data = $query->paginate($value_per_page);

        //add query string to all response links
        $data->appends($request->query());

        return response()->json($data);
    }

    public function getDetailPerlengkapan($id)
    {
        $item = PerlengkapanKebersihan::find($id);
        if (!$item) {
            return response()->json(['message' => 'Perlengkapan tidak ditemukan'], 404);
        }
        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'code' => 'PK-' . str_pad($item->id, 4, '0', STR_PAD_LEFT),
            'stock' => $item->stock,
            'satuan' => $item->satuan,
            'expired' => null,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ]);
    }
}

