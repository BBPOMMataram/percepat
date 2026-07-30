<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\Atk;
use App\Models\PenerimaanAtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class PenerimaanAtkController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = PenerimaanAtk::with('atk')->whereHas(
            'atk',
            function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
            }
        );

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

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            'name' => $name_query,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return response()->json($data);
    }

    function exportPdf(Request $request)
    {
        $perPage   = $request->query('per_page', 10);
        $page      = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = PenerimaanAtk::with('atk')->whereHas(
            'atk',
            function ($query) use ($nameQuery) {
                $query->where('name', 'like', '%' . $nameQuery . '%');
            }
        );

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

        $pdf = PDF::loadView('pdf.penerimaan-atk-export', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("penerimaan-atk-hal{$page}.pdf");
    }

    function store(Request $request)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            // 'tglExpired' => 'nullable|date',
        ]);

        // return $request->all();
        DB::transaction(function () use ($request) {
            // $barangWithNameAndExpiredSameSelected = Atk::where('name', $request->barangNama)
            //     ->where('expired', $request->tglExpired)->first();

            // INI UNTUK AMBIL NAMA DAN SATUAN UNTUK DATA BARANG BARU JIKA TIDAK ADA NAMA & EXPIRED YG SAMA DI MASTER BARANG
            $barangSelected = Atk::where('name', $request->barangNama)->first();

            $barangSelected->increment('stock', $request->jumlah);
            $newBarangId = $barangSelected->id;

            // JIKA ADA BARANG YG SAMA NAMA DAN EXPIRED MAKA HANYA UPDATE STOCK
            // if ($barangWithNameAndExpiredSameSelected) {
            //     $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
            //     $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            // } else {
            // JIKA TIDAK MAKA BUAT BARANG BARU
            // $barangNew = Atk::create([
            //     'name' => $barangSelected['name'],
            //     'satuan' => $barangSelected['satuan'],
            //     'stock' => $request->jumlah,
            // 'expired' => $request->tglExpired
            // ]);
            // $newBarangId = $barangNew->id;
            // }

            PenerimaanAtk::create([
                'atk_id' => $newBarangId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, PenerimaanAtk $penerimaan_atk)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            // 'tglExpired' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $penerimaan_atk) {
            // First, reverse the old stock addition (like destroy)
            $oldBarang = $penerimaan_atk->atk;
            if ($oldBarang) {
                $oldBarang->decrement('stock', $penerimaan_atk->jumlah);
            }

            // Now handle new barang like in store
            // $barangWithNameAndExpiredSameSelected = Atk::where('name', $request->barangNama)
            //     ->where('expired', $request->tglExpired)->first();

            $barangSelected = Atk::where('name', $request->barangNama)->first();

            $barangSelected->increment('stock', $request->jumlah);
            $newBarangId = $barangSelected->id;
            // if ($barangWithNameAndExpiredSameSelected) {
            //     // Update stock of existing matching barang
            //     $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
            //     $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            // } else {
            //     // Create new barang
            //     $barangNew = Atk::create([
            //         'name' => $barangSelected->name,
            //         'satuan' => $barangSelected->satuan,
            //         'stock' => $request->jumlah,
            //         'expired' => $request->tglExpired
            //     ]);
            //     $newBarangId = $barangNew->id;
            // }

            // Update the PenerimaanAtk record
            $penerimaan_atk->update([
                'atk_id' => $newBarangId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(PenerimaanAtk $penerimaan_atk)
    {
        DB::transaction(function () use ($penerimaan_atk) {
            if ($penerimaan_atk->atk) {
                if ($penerimaan_atk->atk->stock < $penerimaan_atk->jumlah) {
                    throw new \Exception('Stok tidak mencukupi untuk dibatalkan.');
                }
                $penerimaan_atk->atk->decrement('stock', $penerimaan_atk->jumlah);
            }
            $penerimaan_atk->delete();
        });

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
