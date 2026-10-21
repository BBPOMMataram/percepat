<?php

namespace App\Http\Controllers;

use App\Models\BakuPembanding;
use Illuminate\Http\Request;

class BakuPembandingController extends Controller
{
    // FOR PUBLIC API (HOMEPAGE)
    function getDataBakuPembanding(Request $request)
    {
        $value_per_page = $request->query('value_per_page', 10);
        $name_query = $request->query('name');

        $query = BakuPembanding::query();

        if ($name_query) {
            $query->where(function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
            });
        }

        $data = $query->paginate($value_per_page);

        $data->appends(['value_per_page' => $value_per_page]);
        $data->appends(['name' => $name_query]);

        return response()->json($data);
    }

    public function getDetailBakuPembanding($id)
    {
        $barang = BakuPembanding::find($id);
        if (!$barang) {
            return response()->json(['message' => 'Baku Pembanding tidak ditemukan'], 404);
        }

        // Get latest completed and delivered permintaan for this baku pembanding
        $latestPermintaanList = \App\Models\PermintaanListBakuPembanding::with(['permintaan.peminta'])
            ->where('baku_pembanding_id', $id)
            ->whereHas('permintaan', function ($q) {
                $q->where('status_id', 4)
                  ->whereNotNull('tgl_penyerahan');
            })
            ->latest('id')
            ->first();

        $permintaan = $latestPermintaanList?->permintaan;
        $diterimaTanggal = $permintaan?->tgl_penyerahan;
        $diterimaPenerima = $permintaan?->peminta?->name;

        return response()->json([
            'id' => $barang->id,
            'name' => $barang->name,
            'code' => $barang->code,
            'kode_baru' => $barang->kode_baru,
            'letak' => $barang->letak,
            'stock' => $barang->stock,
            'satuan' => $barang->satuan,
            'expired' => $barang->expired ? $barang->expired->format('Y-m-d') : null,
            'msds' => $barang->msds,
            'diterima_tanggal' => $diterimaTanggal ? \Carbon\Carbon::parse($diterimaTanggal)->format('Y-m-d') : null,
            'diterima_penerima' => $diterimaPenerima,
            'created_at' => $barang->created_at,
            'updated_at' => $barang->updated_at,
        ]);
    }
}
