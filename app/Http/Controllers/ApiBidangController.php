<?php

namespace App\Http\Controllers;

use App\Http\Resources\BidangResource;
use App\Models\ApiBidang;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiBidangController extends Controller
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

        $data = ApiBidang::where('name', 'like', '%' . $name_query . '%');

        if ($limit_query) { //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
            $data = $data->limit($limit_query)->latest()->get();
        } else {
            $data = $data->latest()->paginate($value_per_page_query);
            //add query string to all response links (KALAU ADA QUERY STRING NYA SAAT PERTAMA FETCH DATA)
            $data->appends(['value_per_page' => $value_per_page_query]);
            $data->appends(['name' => $name_query]);
        }

        return BidangResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $this->validate($request, [
            'name' => ['required', 'unique:'. Bidang::class],
            'kabid' => 'required'
        ]);

        $data = new Bidang();
        
        $data->name = $validated['name'];
        $data->kabid = $validated['kabid'];

        $data->save();

        return response()->json(['msg' => 'Data berhasil tersimpan!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiBidang  $bidang
     * @return \Illuminate\Http\Response
     */
    public function show(ApiBidang $bidang)
    {
        return new BidangResource($bidang);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiBidang  $bidang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiBidang $bidang)
    {
        $validated = $this->validate($request, [
            'name' => ['required', Rule::unique(Bidang::class)->ignore($bidang)],
            'kabid' => 'required'
        ]);

        $bidang->name = $validated['name'];
        $bidang->kabid = $validated['kabid'];

        $bidang->save();

        return response()->json(['msg' => 'Data berhasil diubah!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiBidang  $bidang
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiBidang $bidang)
    {
        $bidang->delete();
        return response()->json(['msg' => 'Data berhasil dihapus!']);
    }

    function getAll() {
        $data = Bidang::all();
        
        return BidangResource::collection($data);
    }

    function bidang_count() {
        $data = Bidang::count();
        return response()->json($data);
    }
}
