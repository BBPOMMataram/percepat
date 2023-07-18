<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = Pembelian::whereHas(
            'barang',
            function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
            }
        )->with('barang')->limit($limit_query)->latest()->paginate($value_per_page_query);

        //add query string to all response links
        $data->appends(['value_per_page' => $value_per_page_query]);
        $data->appends(['name' => $name_query]);

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            $data = Pembelian::whereHas(
                'barang',
                function ($query) use ($name_query) {
                    $query->where('name', 'like', '%' . $name_query . '%');
                }
            )->with('barang')->limit($limit_query)->latest()->get();
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
        // return $request->all();
        $this->validate($request, [
            'barangs_id' => 'required',
            'jumlah' => 'numeric|min:1',
        ], [
            'barangs_id.required' => 'Silahkan Pilih Reagen',
            'jumlah.numeric' => 'Format Jumlah harus number',
            'jumlah.min' => 'Jumlah minimal 1',
        ]);

        DB::transaction(function () use ($request) {

            $data = new Pembelian();
            $data->barangs_id = $request->barangs_id;
            $data->expired = $request->expired;
            $data->jumlah = $request->jumlah;
            $data->vendor = $request->vendor;
            $data->created_at = $request->created_at; //new tgl pembelian

            $data->save();
            
            $barang = Barang::find($request->barangs_id);
            if (isset($barang->expired)) {
                $barangExpired = $barang->expired->format('Y-m-d');
            } else {
                $barangExpired = $barang->expired;
            }

            // jika expired barang yang diinput sama dengan yang sudah ada di gudang maka jumlahkan stok...
            if ($request->expired === $barangExpired) {
                $barang->stock = $barang->stock + $request->jumlah;
                $barang->save();
            } else {
                // ...jika tidak duplikat barang dan bedakan expired dan stok nya
                $newBarang = new Barang();

                $newBarang->code = $barang->code;
                $newBarang->name = $barang->name;
                $newBarang->satuan = $barang->satuan;
                $newBarang->expired = $request->expired; //untuk data barang baru yang beda expired
                $newBarang->stock = $request->jumlah; //untuk data barang baru yang beda expired
                $newBarang->save();

                $data->barangs_id = $newBarang->id; //ubah barangs_id yang diinput dengan barang baru yg beda expired
                $data->save();
            }

        }); //end DB::transaction
        return response(['status' => 1,'msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
