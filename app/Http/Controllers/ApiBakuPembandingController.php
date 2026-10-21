<?php

namespace App\Http\Controllers;

use App\Models\BakuPembanding;
use App\Models\PenerimaanBakuPembanding;
use App\Models\PermintaanListBakuPembanding;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;

class ApiBakuPembandingController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page', 10);
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $query = BakuPembanding::query();

        if ($name_query) {
            $query->where(function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
            });
        }

        if ($limit_query) {
            $data = $query->limit($limit_query)->latest()->get();
            return response()->json(['data' => $data]);
        } else {
            $data = $query->latest()->paginate($value_per_page_query);
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
            return response()->json($data);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'satuan' => ['required'],
            'stock' => ['numeric', 'min:0']
        ]);

        $bakuPembanding = BakuPembanding::where([
            'name' => $request->name,
            'satuan' => $request->satuan,
            'expired' => $request->expired
        ])->first();

        if ($bakuPembanding) {
            $bakuPembanding->stock += $request->stock;
            if ($request->kode_baru) $bakuPembanding->kode_baru = $request->kode_baru;
            if ($request->letak) $bakuPembanding->letak = $request->letak;
            $bakuPembanding->save();
        } else {
            $newBakuPembanding = new BakuPembanding();
            $newBakuPembanding->code = $request->code;
            $newBakuPembanding->kode_baru = $request->kode_baru;
            $newBakuPembanding->name = $request->name;
            $newBakuPembanding->letak = $request->letak;
            $newBakuPembanding->satuan = $request->satuan;
            $newBakuPembanding->msds = $request->msds;
            $newBakuPembanding->expired = $request->expired;
            $newBakuPembanding->stock = $request->stock;
            $newBakuPembanding->save();
        }

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    public function show($id)
    {
        $bakuPembanding = BakuPembanding::findOrFail($id);
        return response()->json(['data' => $bakuPembanding]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required'],
            'satuan' => ['required'],
            'stock' => ['numeric', 'min:0']
        ]);

        $barang = BakuPembanding::findOrFail($id);
        $barang->code = $request->code ?? $barang->code;
        $barang->kode_baru = $request->kode_baru ?? $barang->kode_baru;
        $barang->name = $request->name;
        $barang->letak = $request->letak ?? $barang->letak;
        $barang->satuan = $request->satuan;
        $barang->msds = $request->msds ?? $barang->msds;
        $barang->expired = $request->expired;
        $barang->stock = $request->stock;
        $barang->save();

        return response()->json(['msg' => 'Data berhasil diubah!']);
    }

    public function destroy($id)
    {
        $barang = BakuPembanding::findOrFail($id);
        $barang->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    public function getAll(Request $request)
    {
        $name_query = $request->query('name');
        $isGroupByName = $request->query('groupbyname', false);

        $query = BakuPembanding::query();

        if ($name_query) {
            $query->where(function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
            });
        }

        if ($isGroupByName) {
            $query = $query->select('name')->distinct();
        }

        return response()->json($query->get());
    }

    public function bakuPembandingExpired(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page', 10);
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $query = BakuPembanding::whereDate('expired', '<', now()->addMonths(6))
            ->where('stock', '>', 0)
            ->orderBy('expired');

        if ($name_query) {
            $query->where(function ($q) use ($name_query) {
                $q->where('name', 'like', '%' . $name_query . '%')
                  ->orWhere('kode_baru', 'like', '%' . $name_query . '%')
                  ->orWhere('letak', 'like', '%' . $name_query . '%');
            });
        }

        if ($limit_query) {
            $data = $query->limit($limit_query)->latest()->get();
            return response()->json(['data' => $data]);
        } else {
            $data = $query->latest()->paginate($value_per_page_query);
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
            return response()->json($data);
        }
    }

    public function bakuPembandingExpiredCount()
    {
        $count = BakuPembanding::whereDate('expired', '<', now()->addMonths(6))
            ->where('stock', '>', 0)->count();

        return response()->json($count);
    }

    public function downloadBakuPembanding()
    {
        $data = BakuPembanding::all();

        $pdf = PDF::loadView(
            'pdf/barang-baku-pembanding',
            compact('data')
        )->setPaper('a4', 'landscape');

        return $pdf->download('data-baku-pembanding.pdf');
    }

    public function downloadKartuStokBakuPembanding($id)
    {
        $barang = BakuPembanding::findOrFail($id);

        // Get penerimaan (masuk)
        $penerimaan = PenerimaanBakuPembanding::where('baku_pembanding_id', $id)
            ->whereNotNull('created_at')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->created_at->toDateTimeString(),
                    'keterangan' => 'Pengadaan (' . ($item->vendor ?? '-') . ')',
                    'tipe' => 'masuk',
                    'jumlah' => (int) $item->jumlah,
                ];
            });

        // Get permintaan (keluar)
        $permintaan = PermintaanListBakuPembanding::where('baku_pembanding_id', $id)
            ->whereNotNull('created_at')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'tanggal' => $item->created_at->toDateTimeString(),
                    'keterangan' => $item->keterangan ?? 'Permintaan',
                    'tipe' => 'keluar',
                    'jumlah' => (int) ($item->jumlahrealisasi ?? $item->jumlahpermintaan),
                ];
            });

        // Merge and sort by date
        $transaksi = $penerimaan->concat($permintaan)->sortBy('tanggal')->values();

        $pdf = PDF::loadView('pdf.kartu-stok-baku-pembanding', [
            'barang' => $barang,
            'transaksi' => $transaksi,
        ]);

        return $pdf->download('kartu-stok-baku-pembanding-' . $barang->name . '.pdf');
    }
}
