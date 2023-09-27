<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListPermintaanAtkResource;
use App\Models\ApiUser;
use App\Models\Atk;
use App\Models\Permintaan;
use App\Models\PermintaanListAtk;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanListAtkController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $limit_query = $request->query('limit');

        $data = Permintaan::with('peminta', 'status', 'bidang', 'bidang.user')
            ->where('jenis', 'ATK');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
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
            $data->jenis = 'ATK';

            $last_data = Permintaan::where('jenis', 'ATK')
                ->latest()->first();

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
                $newInventory = new PermintaanListAtk();
                $newInventory->permintaan_id = $data->id; //permintaan id
                $newInventory->atk_id = $value->barang->id;
                $newInventory->jumlahpermintaan = $value->jumlahpermintaan;
                $newInventory->keterangan = $value->keterangan;

                $newInventory->save();
            }
        });
        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    public function update(Request $request, Permintaan $permintaan_atk)
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

        DB::transaction(function () use ($request, $inventory, $permintaan_atk) {

            $permintaan_atk->tgl_permintaan = $request->created_at;

            $permintaan_atk->save();

            // UPDATE LIST BARANG
            PermintaanListAtk::where('permintaan_id', $permintaan_atk->id)->delete();
            foreach ($inventory as $value) {
                $newInventory = new PermintaanListAtk();
                $newInventory->permintaan_id = $permintaan_atk->id;
                $newInventory->atk_id = isset($value->barang) ? $value->barang->id : $value->atk->id; // REQUEST NAMA BARANG DI ATK BERBEDA KARENA RESPONSE SAAT FETCH LIST INVENTORY NAMANYA ATK JADI DATA YANG DI FETCH NAMANYA ATK DAN YG DITAMBAHKAN SAAT EDIT NAMANYA BARANG
                $newInventory->jumlahpermintaan = $value->jumlahpermintaan;
                $newInventory->keterangan = $value->keterangan;

                $newInventory->save();
            }
        });
        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permintaan $permintaan_atk)
    {
        $permintaan_atk->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    function listPermintaanAtk(Permintaan $permintaan)
    {
        $data = PermintaanListAtk::with('atk', 'permintaan')
            ->where('permintaan_id', $permintaan->id)
            ->get();

        return new ListPermintaanAtkResource($data);
    }

    function addListPermintaanAtk(Request $request, Permintaan $permintaan)
    {
        $this->validate($request, [
            'barang_id' => 'required',
            'jumlahpermintaan' => 'numeric|min:1',
        ], [], [
            'barang_id' => 'Nama barang'
        ]);

        $existingData = PermintaanListAtk::where('permintaan_id', $permintaan->id)
            ->where('barang_id', $request->barang_id)->first();

        if ($existingData) {
            $existingData->jumlahpermintaan += $request->jumlahpermintaan;
            $existingData->keterangan = $request->keterangan;
            $existingData->save();
        } else {
            $data = new PermintaanListAtk();
            $data->permintaan_id = $permintaan->id;
            $data->barang_id = $request->barang_id;
            $data->jumlahpermintaan = $request->jumlahpermintaan;
            $data->keterangan = $request->keterangan;
            $data->save();
        }

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    function removeListPermintaanAtk(Permintaan $permintaan, Atk $atk)
    {

        PermintaanListAtk::where('permintaan_id', $permintaan->id)
            ->where('atk_id', $atk->id)->delete();

        return response()->json(['status' => 1, 'msg' => 'Berhasil dihapus!']);
    }

    function downloadPermintaanAtk($id)
    {
        $datapermintaan = Permintaan::find($id);
        $datapermintaanlist = PermintaanListAtk::where('permintaan_id', $id)->get();
        $penyerah = ApiUser::where('position', 'penyerah')->first();
        $kasub = ApiUser::where('position', 'kasubbagumum')->first();
        $pemohon = ApiUser::find($datapermintaan->created_by);
        $kabid = ApiUser::find($datapermintaan->bidang->user->id);
        $penyerahSignature = 'storage/' . $penyerah->getRawOriginal('signature');
        $kasubSignature = 'storage/' . $kasub->getRawOriginal('signature');
        $pemohonSignature = 'storage/' . $pemohon->getRawOriginal('signature');
        $kabidSignature = 'storage/' . $kabid->getRawOriginal('signature');

        $pdf = PDF::loadView('pdf/permintaan-atk', compact(
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
