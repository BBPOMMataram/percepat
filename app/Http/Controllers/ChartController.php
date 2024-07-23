<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReagenResource;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    function reagen(Request $request) {
        $reagen = Barang::paginate();


        $year = $request->query("year");
        if($year){
            $reagen = Barang::whereYear('created_at', $year)
                        ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as sales'))
                        ->groupBy(DB::raw('MONTH(created_at)'))
                        ->get();

        // Mengubah data menjadi format yang diinginkan
        $data = [
            'year' => $year,
            'reagen' => $reagen->map(function($item) {
                return [
                    'month' => date("F", mktime(0, 0, 0, $item->month, 10)), // Mengubah angka bulan menjadi nama bulan
                    'sales' => $item->sales
                ];
            })
        ];

        return response()->json($data);
        }

        return ReagenResource::collection($reagen);
    }
}
