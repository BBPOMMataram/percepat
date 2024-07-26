<?php

namespace App\Http\Controllers;

use App\Http\Resources\LaporanPermintaanReagenResource;
use App\Http\Resources\ReagenResource;
use App\Models\Atk;
use App\Models\Barang;
use App\Models\PermintaanList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    function barang(Request $request)
    {
        $reagen = Barang::sum('stock');
        $atk = Atk::sum('stock');

        $total = [
            'reagen' => $reagen,
            'atk' => $atk,
        ];
        return new ReagenResource(["total" => $total]);
    }

    function permintaan(Request $request)
    {
        $permintaan_reagen = PermintaanList::paginate();

        $year = $request->query("year");
        if ($year) {
            $permintaan_reagen = PermintaanList::whereYear('created_at', $year)
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('sum(jumlahpermintaan) as jumlah_permintaan'),
                    DB::raw('sum(jumlahrealisasi) as jumlah_realisasi')
                )
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get();

            // Mengubah data menjadi format yang diinginkan
            $data = [
                'year' => $year,
                'reagen' => $permintaan_reagen->map(function ($item) {
                    return [
                        'month' => date("F", mktime(0, 0, 0, $item->month, 10)), // Mengubah angka bulan menjadi nama bulan
                        'jumlah_permintaan' => $item->jumlah_permintaan,
                        'jumlah_realisasi' => $item->jumlah_realisasi,
                    ];
                })
            ];

            return response()->json($data);
        }

        return LaporanPermintaanReagenResource::collection($permintaan_reagen);
    }

    function reagen_ed(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = Barang::where('name', 'like', '%' . $name_query . '%')
            ->whereDate('expired', '<', now()->addMonths(6))
            ->where('stock', '>', 0)
            ->orderBy('expired');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return ReagenResource::collection($data);
    }
}
