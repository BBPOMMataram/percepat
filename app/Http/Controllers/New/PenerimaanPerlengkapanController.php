<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\PenerimaanPerlengkapanKebersihan;
use App\Models\PerlengkapanKebersihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanPerlengkapanController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = PenerimaanPerlengkapanKebersihan::with('barang')->whereHas(
            'barang',
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
            // $barangWithNameAndExpiredSameSelected = PerlengkapanKebersihan::where('name', $request->barangNama)
            //     ->where('expired', $request->tglExpired)->first();

            // INI UNTUK AMBIL NAMA DAN SATUAN UNTUK DATA BARANG BARU JIKA TIDAK ADA NAMA & EXPIRED YG SAMA DI MASTER BARANG
            $barangSelected = PerlengkapanKebersihan::where('name', $request->barangNama)->first();

            $barangSelected->increment('stock', $request->jumlah);
            $newBarangId = $barangSelected->id;

            // JIKA ADA BARANG YG SAMA NAMA DAN EXPIRED MAKA HANYA UPDATE STOCK
            // if ($barangWithNameAndExpiredSameSelected) {
            //     $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
            //     $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            // } else {
            // JIKA TIDAK MAKA BUAT BARANG BARU
            // $barangNew = PerlengkapanKebersihan::create([
            //     'name' => $barangSelected['name'],
            //     'satuan' => $barangSelected['satuan'],
            //     'stock' => $request->jumlah,
            // 'expired' => $request->tglExpired
            // ]);
            // $newBarangId = $barangNew->id;
            // }

            PenerimaanPerlengkapanKebersihan::create([
                'perlengkapan_kebersihan_id' => $newBarangId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, PenerimaanPerlengkapanKebersihan $penerimaan_perlengkapan)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            // 'tglExpired' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $penerimaan_perlengkapan) {
            // First, reverse the old stock addition (like destroy)
            $oldBarang = $penerimaan_perlengkapan->barang;
            if ($oldBarang) {
                $oldBarang->decrement('stock', $penerimaan_perlengkapan->jumlah);
            }

            // Now handle new barang like in store
            // $barangWithNameAndExpiredSameSelected = PerlengkapanKebersihan::where('name', $request->barangNama)
            //     ->where('expired', $request->tglExpired)->first();

            $barangSelected = PerlengkapanKebersihan::where('name', $request->barangNama)->first();

            $barangSelected->increment('stock', $request->jumlah);
            $newBarangId = $barangSelected->id;
            // if ($barangWithNameAndExpiredSameSelected) {
            //     // Update stock of existing matching barang
            //     $barangWithNameAndExpiredSameSelected->increment('stock', $request->jumlah);
            //     $newBarangId = $barangWithNameAndExpiredSameSelected->id;
            // } else {
            //     // Create new barang
            //     $barangNew = PerlengkapanKebersihan::create([
            //         'name' => $barangSelected->name,
            //         'satuan' => $barangSelected->satuan,
            //         'stock' => $request->jumlah,
            //         'expired' => $request->tglExpired
            //     ]);
            //     $newBarangId = $barangNew->id;
            // }

            // Update the PenerimaanPerlengkapanKebersihan record
            $penerimaan_perlengkapan->update([
                'perlengkapan_kebersihan_id' => $newBarangId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(PenerimaanPerlengkapanKebersihan $penerimaan_perlengkapan)
    {
        DB::transaction(function () use ($penerimaan_perlengkapan) {
            if ($penerimaan_perlengkapan->barang) {
                if ($penerimaan_perlengkapan->barang->stock < $penerimaan_perlengkapan->jumlah) {
                    throw new \Exception('Stok tidak mencukupi untuk dibatalkan.');
                }
                $penerimaan_perlengkapan->barang->decrement('stock', $penerimaan_perlengkapan->jumlah);
            }
            $penerimaan_perlengkapan->delete();
        });

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
