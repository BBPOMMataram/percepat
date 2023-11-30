<?php

namespace App\Http\Controllers;

use App\Http\Resources\LaporanPermintaanAtkResource;
use App\Http\Resources\LaporanPermintaanReagenResource;
use App\Models\PermintaanList;
use App\Models\PermintaanListAtk;
use Barryvdh\DomPDF\Facade as PDF;
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

    public function permintaanAtk(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        // PERMINTAAN LIST UNTUK ATK NAMA MODELNYA PermintaanListAtk
        $data = PermintaanListAtk::with('atk', 'permintaan');

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

        return new LaporanPermintaanAtkResource($data);
    }

    function downloadLaporanPermintaanReagen()
    {
        $datapermintaanlist = PermintaanList::with('barang', 'permintaan.peminta', 'permintaan.bidang', 'permintaan.status', 'permintaan')->get();
        // if ($id) {
        //     $datapermintaanlist = $datapermintaanlist->where('barang_id', $id);
        // }
        // $kabid = User::find(auth()->user()->id);
        $pdf = PDF::loadView(
            'pdf/laporan',
            compact(
                'datapermintaanlist',
            )
        );
        return $pdf->download();
    }
}
