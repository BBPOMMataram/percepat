<?php

namespace App\Http\Controllers;

use App\Models\Atk;
use App\Models\PenerimaanAtk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenerimaanAtkController extends Controller
{
    function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = PenerimaanAtk::whereHas(
            'atk',
            function ($query) use ($name_query) {
                $query->where('name', 'like', '%' . $name_query . '%');
            }
        )->with('atk')->latest()->paginate($value_per_page_query);

        //add query string to all response links
        $data->appends(['value_per_page' => $value_per_page_query]);
        $data->appends(['name' => $name_query]);

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            $data = PenerimaanAtk::whereHas(
                'atk',
                function ($query) use ($name_query) {
                    $query->where('name', 'like', '%' . $name_query . '%');
                }
            )->with('atk')->limit($limit_query)->latest()->get();
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        // return $request->all();
        $this->validate($request, [
            'atk_id' => 'required',
            'jumlah' => 'numeric|min:1',
        ], [
            'atk_id.required' => 'Silahkan Pilih Atk',
            'jumlah.numeric' => 'Format Jumlah harus number',
            'jumlah.min' => 'Jumlah minimal 1',
        ]);

        DB::transaction(function () use ($request) {

            $data = new PenerimaanAtk();
            $data->atk_id = $request->atk_id;
            $data->jumlah = $request->jumlah;
            $data->vendor = $request->vendor;
            $data->created_at = $request->created_at; //new tgl pembelian

            $data->save();

            $atk = Atk::find($request->atk_id);

            $atk->stock = $atk->stock + $request->jumlah;
            $atk->save();
        }); //end DB::transaction

        return response(['status' => 1, 'msg' => 'Data berhasil tersimpan!']);
    }
}
