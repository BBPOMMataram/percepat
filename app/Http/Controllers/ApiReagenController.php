<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReagenResource;
use App\Models\ApiReagen;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiReagenController extends Controller
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

        $data = ApiReagen::where('name', 'like', '%' . $name_query . '%');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return ReagenResource::collection($data);
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
            'name' => ['required', 'bail', Rule::unique(ApiReagen::class)],
            'satuan' => ['required']
        ]);

        $reagen = ApiReagen::where([
            'name' => $request->name,
            'satuan' => $request->satuan,
            'expired' => $request->expired
        ])->first();

        // jika expired, satuan dan nama barang yang diinput sama maka jumlahkan stok...
        if ($reagen) {
            $reagen->stock += $request->stock;

            $reagen->save();
        } else {
            // ...jika tidak input barang baru
            $newReagen = new ApiReagen();

            $newReagen->code = $request->code;
            $newReagen->name = $request->name;
            $newReagen->satuan = $request->satuan;
            $newReagen->msds = $request->msds;
            $newReagen->expired = $request->expired;
            $newReagen->stock = $request->stock;

            $newReagen->save();
        }

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiReagen  $barang_reagen
     * @return \Illuminate\Http\Response
     */
    public function show(ApiReagen $barang_reagen)
    {
        return new ReagenResource($barang_reagen);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiReagen  $barang_reagen
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiReagen $barang_reagen)
    {
        $this->validate($request, [
            'name' => ['required', 'bail'],
            'satuan' => ['required']
        ]);

        $existingReagen = ApiReagen::where([
            'name' => $request->name,
            'satuan' => $request->satuan,
            'expired' => $request->expired
        ])->first();

        // jika expired, satuan dan nama barang yang diinput sama maka jumlahkan stok...
        if ($existingReagen && $existingReagen->id !== $barang_reagen->id) {
            $barang_reagen->delete();
            $existingReagen->stock += $request->stock;

            $existingReagen->save();

            return response()->json(['msg' => 'Data berhasil diubah! (duplikat)']);
        } else {
            // ...jika tidak input barang baru

            $barang_reagen->code = $request->code;
            $barang_reagen->name = $request->name;
            $barang_reagen->satuan = $request->satuan;
            $barang_reagen->msds = $request->msds;
            $barang_reagen->expired = $request->expired;
            $barang_reagen->stock = $request->stock;

            $barang_reagen->save();

            return response()->json(['msg' => 'Data berhasil diubah!']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiReagen  $barang_reagen
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiReagen $barang_reagen)
    {
        $barang_reagen->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    // COBA CEK ROUTE YANG MENGARAH KE FUNGSI INI APAKAH BISA DIARAHKAN SAJA KE INDEX KARENA SAMA FUNGSI
    // ROUTE INI UNTUK REACT SELECT OPTION
    function getAll(Request $request)
    {
        $name_query = $request->query('name');

        $responseReagen = ApiReagen::where('name', 'like', '%' . $name_query . '%')->get();
        return response()->json($responseReagen);
    }

    public function reagenExpired(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = ApiReagen::where('name', 'like', '%' . $name_query . '%')
                ->whereDate('expired', '<', now()->addMonths(6))
                ->where('stock', '>', 0)
                ->orderBy('expired');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return ReagenResource::collection($data);
    }

    
    public function reagenExpiredCount()
    {
        $data = ApiReagen::whereDate('expired', '<', now()->addMonths(6))
                ->where('stock', '>', 0)->count();
        
        return $data;
    }
}
