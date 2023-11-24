<?php

namespace App\Http\Controllers;

use App\Http\Resources\LaporanPermintaanReagenResource;
use App\Http\Resources\PermintaanReagenResource;
use App\Models\Permintaan;
use App\Models\PermintaanList;
use Illuminate\Http\Request;

class LaporanPermintaanController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        // PERMINTAAN LIST REAGEN, UNTUK ATK NAMA MODELNYA PermintaanListAtk
        $data = PermintaanList::with('barang', 'permintaan');

        $year = $request->query('year');
        $month = $request->query('month');
        $bidang = $request->query('bidang');

        // FILTER TAHUN
        if ($year !== 'undefined') {
            $data = $data->whereYear('created_at', $year);
            if ($month !== 'undefined') {
                $data = $data->whereMonth('created_at', $month);
            }
        }

        if ($bidang !== 'undefined') {
            $data = $data->whereHas('permintaan', function ($query) use ($bidang) {
                $query->where('bidang_id', $bidang);
            });
        }

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);

            //add query string to all response links
            $data->appends(['value_per_page' => $value_per_page_query]);
        }

        return new LaporanPermintaanReagenResource($data);
    }
}
