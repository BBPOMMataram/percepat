<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Illuminate\Http\Request;

class PenerimaanController extends Controller
{
    public function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = Pembelian::paginate($value_per_page_query);
        //add query string to all response links
        $data->appends(['value_per_page' => $value_per_page_query]);
        $data->appends(['name' => $name_query]);

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            $data = Pembelian::with('barangreagen')->limit($limit_query)->get();
            if ($name_query) {
                $data = Pembelian::whereHas(
                    'barangreagen',
                    function ($query) use ($name_query) {
                        $query->where('name', 'like', '%' . $name_query . '%');
                    }
                )->with('barangreagen')->limit($limit_query)->get();
            }
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
        //
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
