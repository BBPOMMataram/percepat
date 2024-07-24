<?php

namespace App\Http\Controllers;

use App\Http\Resources\LaporanPermintaanReagenResource;
use App\Http\Resources\ReagenResource;
use App\Models\Barang;
use App\Models\PermintaanList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    function reagen(Request $request)
    {
        $reagen = Barang::paginate();

        $year = $request->query("year");
        if ($year) {
            $reagen = Barang::whereYear('created_at', $year)
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->get();

            // Mengubah data menjadi format yang diinginkan
            $data = [
                'year' => $year,
                'reagen' => $reagen->map(function ($item) {
                    return [
                        'month' => date("F", mktime(0, 0, 0, $item->month, 10)), // Mengubah angka bulan menjadi nama bulan
                        'total' => $item->total
                    ];
                })
            ];

            return response()->json($data);
        }

        return ReagenResource::collection($reagen);
    }

    function permintaan(Request $request)
    {
        $permintaan_reagen = PermintaanList::paginate();

        $year = $request->query("year");
        if ($year) {
            $permintaan_reagen = PermintaanList::whereYear('created_at', $year)
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('count(jumlahpermintaan) as jumlah_permintaan'),
                    DB::raw('count(jumlahrealisasi) as jumlah_realisasi')
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
}
