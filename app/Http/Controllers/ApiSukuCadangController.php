<?php

namespace App\Http\Controllers;

use App\Models\SukuCadang;
use Illuminate\Http\Request;

class ApiSukuCadangController extends Controller
{
    public function getAll(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $name = $request->query('name');

        $query = SukuCadang::query();

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $data = $query->paginate($perPage);
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = new SukuCadang();
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is added successfully!']);
    }

    public function show($id)
    {
        $data = SukuCadang::find($id);
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'satuan' => 'required',
            'stock' => 'numeric|min:0',
        ]);

        $data = SukuCadang::find($id);
        $data->name = $request->name;
        $data->satuan = $request->satuan;
        $data->stock = $request->stock;
        $data->description = $request->description;
        $data->save();

        return response(['status' => 1, 'data' => $data, 'msg' => 'Data is updated successfully!']);
    }

    public function destroy($id)
    {
        SukuCadang::destroy($id);
        return response()->json(['status' => 1, 'msg' => 'Deleted successfully']);
    }

    public function downloadSukuCadang()
    {
        $data = SukuCadang::all();
        $filename = 'data_suku_cadang_' . date('Y-m-d') . '.csv';
        $handle = fopen($filename, 'w+');
        fputcsv($handle, ['No', 'Kode', 'Nama', 'Satuan', 'Stock', 'Deskripsi']);

        $no = 1;
        foreach ($data as $d) {
            fputcsv($handle, [
                $no++,
                $d->code,
                $d->name,
                $d->satuan,
                $d->stock,
                $d->description,
            ]);
        }

        fclose($handle);

        return response()->download($filename)->deleteFileAfterSend(true);
    }
}
