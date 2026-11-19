<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanSukuCadang;
use App\Models\SukuCadang;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade as PDF;

class PenerimaanSukuCadangController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = PenerimaanSukuCadang::with('sukuCadang');

        if ($name) {
            $query->whereHas('sukuCadang', function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%');
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $data = $query->latest()->paginate($perPage, ['*'], 'page', $page)->appends([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($data);
    }

    public function exportPdf(Request $request)
    {
        $perPage   = (int) $request->query('per_page', 10);
        $page      = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = PenerimaanSukuCadang::with('sukuCadang');

        if ($nameQuery) {
            $query->whereHas('sukuCadang', function ($q) use ($nameQuery) {
                $q->where('name', 'like', '%' . $nameQuery . '%');
            });
        }

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59',
            ]);
        } elseif ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $query->latest();

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        $pdf = PDF::loadView('pdf.penerimaan-suku-cadang-export', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("penerimaan-suku-cadang-hal{$page}.pdf");
    }

    public function store(Request $request)
    {
        $data = new PenerimaanSukuCadang();
        $data->suku_cadang_id = $request->suku_cadang_id;
        $data->jumlah = $request->jumlah;
        $data->vendor = $request->vendor;
        if ($request->tglTerima) {
            $data->created_at = $request->tglTerima;
        }
        $data->save();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = PenerimaanSukuCadang::findOrFail($id);
        if ($request->has('suku_cadang_id')) {
            $data->suku_cadang_id = $request->suku_cadang_id;
        }
        if ($request->has('jumlah')) {
            $data->jumlah = $request->jumlah;
        }
        if ($request->has('vendor')) {
            $data->vendor = $request->vendor;
        }
        if ($request->has('tglTerima')) {
            $data->created_at = $request->tglTerima;
        }
        $data->save();

        return response()->json(['status' => 1, 'data' => $data]);
    }

    public function destroy($id)
    {
        PenerimaanSukuCadang::destroy($id);
        return response()->json(['status' => 1]);
    }
}
