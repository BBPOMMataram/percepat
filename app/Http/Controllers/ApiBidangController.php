<?php

namespace App\Http\Controllers;

use App\Http\Resources\BidangResource;
use App\Models\ApiBidang;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiBidang  $apiBidang
     * @return \Illuminate\Http\Response
     */
    public function show(ApiBidang $apiBidang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiBidang  $apiBidang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiBidang $apiBidang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiBidang  $apiBidang
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiBidang $apiBidang)
    {
        //
    }
}
