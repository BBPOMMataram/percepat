<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\BakuPembanding;
use App\Models\PenerimaanBakuPembanding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;

class PenerimaanBakuPembandingController extends Controller
{
    // UNTUK CRUD ADMIN PERCEPAT PENERIMAAN BAKU PEMBANDING
    function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = PenerimaanBakuPembanding::with('baku_pembanding')->whereHas(
            'baku_pembanding',
            function ($q) use ($name_query) {
                if ($name_query) {
                    $q->where('name', 'like', '%' . $name_query . '%')
                      ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                      ->orWhere('letak', 'like', '%' . $name_query . '%');
                }
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
        $perPage   = (int) $request->query('per_page', 10);
        $page      = $request->query('page', 1);
        $nameQuery = $request->query('name');
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        $query = PenerimaanBakuPembanding::with('baku_pembanding')->whereHas(
            'baku_pembanding',
            function ($q) use ($nameQuery) {
                if ($nameQuery) {
                    $q->where('name', 'like', '%' . $nameQuery . '%')
                      ->orWhere('kode_baru', 'like', '%' . $nameQuery . '%')
                      ->orWhere('letak', 'like', '%' . $nameQuery . '%');
                }
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

        $pdf = PDF::loadView('pdf.penerimaan-baku-pembanding-export', [
            'items'      => $paginated->items(),
            'total'      => $paginated->total(),
            'page'       => $paginated->currentPage(),
            'perPage'    => $paginated->perPage(),
            'nameQuery'  => $nameQuery,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("penerimaan-baku-pembanding-hal{$page}.pdf");
    }

    function store(Request $request)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            'tglExpired' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request) {
            $matchingItem = BakuPembanding::where('name', $request->barangNama)
                ->where('expired', $request->tglExpired)->first();

            $baseItem = BakuPembanding::where('name', $request->barangNama)->first();

            if ($matchingItem) {
                $matchingItem->increment('stock', $request->jumlah);
                $newId = $matchingItem->id;
            } else {
                $newItem = BakuPembanding::create([
                    'code' => $baseItem ? $baseItem->code : null,
                    'kode_baru' => $baseItem ? $baseItem->kode_baru : null,
                    'name' => $baseItem ? $baseItem->name : $request->barangNama,
                    'letak' => $baseItem ? $baseItem->letak : null,
                    'satuan' => $baseItem ? $baseItem->satuan : 'Pcs',
                    'stock' => $request->jumlah,
                    'expired' => $request->tglExpired
                ]);
                $newId = $newItem->id;
            }

            PenerimaanBakuPembanding::create([
                'baku_pembanding_id' => $newId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'expired' => $request->tglExpired,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil tersimpan!'], 201);
    }

    function update(Request $request, PenerimaanBakuPembanding $penerimaan_baku_pembanding)
    {
        $request->validate([
            'barangNama' => 'required',
            'jumlah' => 'required|integer',
            'vendor' => 'required|string|max:255',
            'tglTerima' => 'required|date',
            'tglExpired' => 'nullable|date',
        ]);

        DB::transaction(function () use ($request, $penerimaan_baku_pembanding) {
            $oldItem = $penerimaan_baku_pembanding->baku_pembanding;
            if ($oldItem) {
                $oldItem->decrement('stock', $penerimaan_baku_pembanding->jumlah);
            }

            $matchingItem = BakuPembanding::where('name', $request->barangNama)
                ->where('expired', $request->tglExpired)->first();

            $baseItem = BakuPembanding::where('name', $request->barangNama)->first();

            if ($matchingItem) {
                $matchingItem->increment('stock', $request->jumlah);
                $newId = $matchingItem->id;
            } else {
                $newItem = BakuPembanding::create([
                    'code' => $baseItem ? $baseItem->code : null,
                    'kode_baru' => $baseItem ? $baseItem->kode_baru : null,
                    'name' => $baseItem ? $baseItem->name : $request->barangNama,
                    'letak' => $baseItem ? $baseItem->letak : null,
                    'satuan' => $baseItem ? $baseItem->satuan : 'Pcs',
                    'stock' => $request->jumlah,
                    'expired' => $request->tglExpired
                ]);
                $newId = $newItem->id;
            }

            $penerimaan_baku_pembanding->update([
                'baku_pembanding_id' => $newId,
                'jumlah' => $request->jumlah,
                'vendor' => $request->vendor,
                'expired' => $request->tglExpired,
                'created_at' => $request->tglTerima
            ]);
        });

        return response()->json(['message' => 'Data berhasil diupdate!']);
    }

    function destroy(PenerimaanBakuPembanding $penerimaan_baku_pembanding)
    {
        DB::transaction(function () use ($penerimaan_baku_pembanding) {
            if ($penerimaan_baku_pembanding->baku_pembanding) {
                $penerimaan_baku_pembanding->baku_pembanding->decrement('stock', $penerimaan_baku_pembanding->jumlah);
            }
            $penerimaan_baku_pembanding->delete();
        });

        return response()->json(['message' => 'Data berhasil dihapus!']);
    }
}
