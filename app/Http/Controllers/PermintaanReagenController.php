<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListPermintaanReagenResource;
use App\Models\ApiUser;
use App\Models\Barang;
use App\Models\Permintaan;
use App\Models\PermintaanList;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PermintaanReagenController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        $data = Permintaan::with('peminta', 'status', 'bidang', 'bidang.user');

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links
            $data->appends(['value_per_page' => $value_per_page_query]);
        }

        //data permintaan ditampilkan sesuai jabatan
        if (auth()->user()->position === 'penyelia') {
            $data = $data->where('bidang.user.id', auth()->user()->id);
        } elseif (auth()->user()->position === 'pemohon') {
            $data = $data->where('created_by', auth()->user()->id);
        } elseif (auth()->user()->position === 'penyerah') {
            $data = $data->where('status_id', '>=', 2);
        } elseif (auth()->user()->position === 'kasubbagumum') {
            $data = $data->where('status_id', '>=', 3);
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'userFrontEnd' => ['required'],
            'created_at' => ['required'],
            'inventory' => ['required'],
        ]);

        $inventory = json_decode($request->inventory);
        if (!$inventory) {
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
        }

        DB::transaction(function () use ($request, $inventory) {
            // DI JSON DECODE AGAR DATA PROPERTY PADA OBJECT(KEY) DAPAT TERBACA BUKAN SEBAGAI STRING
            $user = json_decode($request->userFrontEnd);

            $data = new Permintaan();

            $data->bidang_id = $user->bidang->id;
            // $data->kabid_id = $request->kabid_id;
            $data->created_by = $user->id;
            $data->tgl_permintaan = $request->created_at;

            $last_data = Permintaan::latest()->first();

            if ($last_data) {
                if (now()->month !== $last_data->created_at->month) {
                    $data->nourut = 1;
                } else {
                    $data->nourut = $last_data->nourut + 1;
                }
            } else {
                $data->nourut = 1;
            }

            $data->save();

            // STORE LIST BARANG
            foreach ($inventory as $value) {
                $newInventory = new PermintaanList();
                $newInventory->permintaan_id = $data->id; //permintaan id
                $newInventory->barang_id = $value->barang->id;
                $newInventory->jumlahpermintaan = $value->jumlahpermintaan;
                $newInventory->keterangan = $value->keterangan;

                $newInventory->save();
            }
        });
        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    public function update(Request $request, Permintaan $permintaan_reagen)
    {
        $this->validate($request, [
            'userFrontEnd' => ['required'],
            'created_at' => ['required'],
            'inventory' => ['required'],
        ]);

        $inventory = json_decode($request->inventory);
        if (!$inventory) {
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
        }

        DB::transaction(function () use ($request, $inventory, $permintaan_reagen) {

            $permintaan_reagen->tgl_permintaan = $request->created_at;

            $permintaan_reagen->save();

            // UPDATE LIST BARANG
            PermintaanList::where('permintaan_id', $permintaan_reagen->id)->delete();
            foreach ($inventory as $value) {
                $newInventory = new PermintaanList();
                $newInventory->permintaan_id = $permintaan_reagen->id;
                $newInventory->barang_id = $value->barang->id;
                $newInventory->jumlahpermintaan = $value->jumlahpermintaan;
                $newInventory->keterangan = $value->keterangan;

                $newInventory->save();
            }
        });
        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    public function destroy(Permintaan $permintaan_reagen)
    {
        $permintaan_reagen->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    function listPermintaanReagen(Permintaan $permintaan)
    {
        $data = PermintaanList::with('barang', 'permintaan')
            ->where('permintaan_id', $permintaan->id)
            ->get();

        return new ListPermintaanReagenResource($data);
    }

    function addListPermintaanReagen(Request $request, Permintaan $permintaan)
    {
        $this->validate($request, [
            'barang_id' => 'required',
            'jumlahpermintaan' => 'numeric|min:1',
        ], [], [
            'barang_id' => 'Nama barang'
        ]);

        $existingData = PermintaanList::where('permintaan_id', $permintaan->id)
            ->where('barang_id', $request->barang_id)->first();

        if ($existingData) {
            $existingData->jumlahpermintaan += $request->jumlahpermintaan;
            $existingData->keterangan = $request->keterangan;
            $existingData->save();
        } else {
            $data = new PermintaanList();
            $data->permintaan_id = $permintaan->id;
            $data->barang_id = $request->barang_id;
            $data->jumlahpermintaan = $request->jumlahpermintaan;
            $data->keterangan = $request->keterangan;
            $data->save();
        }

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    function removeListPermintaanReagen(Permintaan $permintaan, Barang $barang)
    {

        PermintaanList::where('permintaan_id', $permintaan->id)
            ->where('barang_id', $barang->id)->delete();

        return response()->json(['status' => 1, 'msg' => 'Berhasil dihapus!']);
    }

    function downloadPermintaanReagen($id)
    {
        $datapermintaan = Permintaan::find($id);
        $datapermintaanlist = PermintaanList::where('permintaan_id', $id)->get();
        $penyerah = ApiUser::where('position', 'penyerah')->first();
        $kasub = ApiUser::where('position', 'kasubbagumum')->first();
        $pemohon = ApiUser::find($datapermintaan->created_by);
        $kabid = ApiUser::find($datapermintaan->bidang->user->id);
        $penyerahSignature = 'storage/' . $penyerah->getRawOriginal('signature');
        $kasubSignature = 'storage/' . $kasub->getRawOriginal('signature');
        $pemohonSignature = 'storage/' . $pemohon->getRawOriginal('signature');
        $kabidSignature = 'storage/' . $kabid->getRawOriginal('signature');

        $pdf = PDF::loadView('pdf/permintaan', compact(
            'datapermintaan',
            'datapermintaanlist',
            'penyerah',
            'kasub',
            'pemohon',
            'kabid',
            'penyerahSignature',
            'kasubSignature',
            'pemohonSignature',
            'kabidSignature',
        ));
        return $pdf->download();
    }
}
