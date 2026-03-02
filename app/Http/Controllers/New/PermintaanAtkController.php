<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\Permintaan;
use Illuminate\Http\Request;

class PermintaanAtkController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        $query = Permintaan::with(['peminta', 'status', 'bidang', 'bidang.user', 'katim'])
            ->where('jenis', 'ATK')
            ->latest();

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            // 'kode_or_name' => $kode_or_name
        ]);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'pemohon' => ['required'], // bukan hanya id pemohon tapi data lengkap pemohon dalam bentuk object
            'createdAt' => ['required'],
            'listBarang' => ['required'],
            'katimId' => ['required'],
        ]);

        $listBarang = $request->listBarang;
        if (!$listBarang) {
            return response()->json(['status' => 1, 'msg' => 'Barang tidak boleh kosong !'], 400);
        }

        DB::transaction(function () use ($request, $listBarang) {
            // DI JSON DECODE AGAR DATA PROPERTY PADA OBJECT(KEY) DAPAT TERBACA BUKAN SEBAGAI STRING
            $pemohon = $request->pemohon;

            $data = new Permintaan();

            $data->jenis = 'Reagen dan Bahan Laboratorium Lain';
            $data->bidang_id = null; //dibuat null untuk menyesuaikan bidang user auth (si mandalika), ini untuk permintaan baru setelah SSO
            $data->bidang_id_auth_external = $pemohon['employee']['fungsi_id']; // sbg ganti nya gunakan fungsi dari user auth external  
            $data->bidang_name_auth_external = $pemohon['employee']['fungsi']['name']; // ini untuk langsung simpan nama bidang juga biar gak ribet join ke tabel bidang
            // $data->kabid_id = $request->kabid_id; // cek dulu kenapa di comment, sepertinya nanti diisi setelah diapprove kabid (untuk menjadi kabid siapa yg approve duluan mgkn karena ada beberapa kabid di satu fungsi)
            $data->katim_selected = User::where('external_user_id', $request->katimId)->first()->id; // karena skrg katim pilih manual jadi simpan katim yang dipilih pemohon (setelah SSO)
            $userInternalId = User::where('external_user_id', $pemohon['id'])->first()->id;
            $data->created_by = $userInternalId;
            $data->tgl_permintaan = $request->createdAt;

            // isi no urut
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
            foreach ($listBarang as $value) {
                $newInventory = new PermintaanList();
                $newInventory->permintaan_id = $data->id; //permintaan id
                $newInventory->barang_id = $value['id'];
                $newInventory->jumlahpermintaan = $value['jumlah'];
                $newInventory->keterangan = $value['keterangan'];

                $newInventory->save();
            }
        });

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }
}
