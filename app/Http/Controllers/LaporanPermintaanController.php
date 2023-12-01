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
    // data permintaan reagen
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');
        $name_query = $request->query('name');

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

        if ($name_query) {
            $data = $data->whereHas('barang', function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
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

    // data permintaan atk
    public function permintaanAtk(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');
        $name_query = $request->query('name');

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

        if ($name_query) {
            $data = $data->whereHas('atk', function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
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

    function downloadLaporanPermintaanReagen(Request $request)
    {

        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        // PERMINTAAN LIST REAGEN, UNTUK ATK NAMA MODELNYA PermintaanListAtk
        $datapermintaanlist = PermintaanList::with('barang', 'permintaan.peminta', 'permintaan.bidang', 'permintaan.status', 'permintaan');

        $year = $request->query('year');
        $month = $request->query('month');
        $bidang = $request->query('bidang');

        // FILTER TAHUN
        if ($year !== 'undefined') {
            $datapermintaanlist = $datapermintaanlist->whereYear('created_at', $year);
            if ($month !== 'undefined') {
                $datapermintaanlist = $datapermintaanlist->whereMonth('created_at', $month);
            }
        }

        if ($bidang !== 'undefined') {
            $datapermintaanlist = $datapermintaanlist->whereHas('permintaan', function ($query) use ($bidang) {
                $query->where('bidang_id', $bidang);
            });
        }

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT (IF EXISTS)
        if ($limit_query) {
            $datapermintaanlist = $datapermintaanlist->limit($limit_query)->latest()->get();
        } else {
            $datapermintaanlist = $datapermintaanlist->latest()->paginate($value_per_page_query);
        }

        $pdf = PDF::loadView(
            'pdf/laporan',
            compact('datapermintaanlist')
        );

        return $pdf->download();
    }

    function downloadLaporanPermintaanAtk(Request $request)
    {

        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        // PERMINTAAN LIST REAGEN, UNTUK ATK NAMA MODELNYA PermintaanListAtk
        $datapermintaanlist = PermintaanListAtk::with('atk', 'permintaan.peminta', 'permintaan.bidang', 'permintaan.status', 'permintaan');

        $year = $request->query('year');
        $month = $request->query('month');
        $bidang = $request->query('bidang');

        // FILTER TAHUN
        if ($year !== 'undefined') {
            $datapermintaanlist = $datapermintaanlist->whereYear('created_at', $year);
            if ($month !== 'undefined') {
                $datapermintaanlist = $datapermintaanlist->whereMonth('created_at', $month);
            }
        }

        if ($bidang !== 'undefined') {
            $datapermintaanlist = $datapermintaanlist->whereHas('permintaan', function ($query) use ($bidang) {
                $query->where('bidang_id', $bidang);
            });
        }

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT (IF EXISTS)
        if ($limit_query) {
            $datapermintaanlist = $datapermintaanlist->limit($limit_query)->latest()->get();
        } else {
            $datapermintaanlist = $datapermintaanlist->latest()->paginate($value_per_page_query);
        }

        $pdf = PDF::loadView(
            'pdf/laporan-atk',
            compact('datapermintaanlist')
        );

        return $pdf->download();
    }
}
