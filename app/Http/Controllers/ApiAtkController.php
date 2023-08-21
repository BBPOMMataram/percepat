<?php

namespace App\Http\Controllers;

use App\Http\Resources\AtkResource;
use App\Models\ApiAtk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiAtkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = ApiAtk::where('name', 'like', '%' . $name_query . '%');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return AtkResource::collection($data);
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
            'name' => ['required', 'bail', 'unique:atks'],
            'satuan' => ['required']
        ]);

        $reagen = ApiAtk::where([
            'name' => $request->name,
            'satuan' => $request->satuan,
        ])->first();

        // jika expired, satuan dan nama barang yang diinput sama maka jumlahkan stok...
        if ($reagen) {
            $reagen->stock += $request->stock;

            $reagen->save();
        } else {
            // ...jika tidak input barang baru
            $newReagen = new ApiAtk();

            $newReagen->code = $request->code;
            $newReagen->name = $request->name;
            $newReagen->satuan = $request->satuan;
            $newReagen->stock = $request->stock;

            $newReagen->save();
        }

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiAtk  $barang_atk
     * @return \Illuminate\Http\Response
     */
    public function show(ApiAtk $barang_atk)
    {
        return new AtkResource($barang_atk);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiAtk  $barang_atk
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiAtk $barang_atk)
    {

        $this->validate($request, [
            'name' => ['required', 'bail', Rule::unique(ApiAtk::class)->ignore($barang_atk)],
            'satuan' => ['required']
        ]);
        
        $existingReagen = ApiAtk::where([
            'name' => $request->name,
            'satuan' => $request->satuan,
        ])->first();

        // jika expired, satuan dan nama barang yang diinput sama maka jumlahkan stok...
        if ($existingReagen && $existingReagen->id !== $barang_atk->id) {
            $barang_atk->delete();
            $existingReagen->stock += $request->stock;

            $existingReagen->save();

            return response()->json(['msg' => 'Data berhasil diubah! (duplikat)']);
        } else {
            // ...jika tidak input barang baru

            $barang_atk->code = $request->code;
            $barang_atk->name = $request->name;
            $barang_atk->satuan = $request->satuan;
            $barang_atk->stock = $request->stock;

            $barang_atk->save();

            return response()->json(['msg' => 'Data berhasil diubah!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiAtk  $barang_atk
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiAtk $barang_atk)
    {
        $barang_atk->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    // COBA CEK ROUTE YANG MENGARAH KE FUNGSI INI APAKAH BISA DIARAHKAN SAJA KE INDEX KARENA SAMA FUNGSI
    // ROUTE INI UNTUK REACT SELECT OPTION
    function getAll(Request $request)
    {
        $name_query = $request->query('name');

        $responseReagen = ApiAtk::where('name', 'like', '%' . $name_query . '%')->get();
        return response()->json($responseReagen);
    }
}
