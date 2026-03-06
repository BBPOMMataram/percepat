<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\PerlengkapanKebersihan;
use Illuminate\Http\Request;

class PerlengkapanKebersihanController extends Controller
{
    public function get_data_perlengkapan(Request $request)
    {
        $value_per_page = $request->query('value_per_page');
        $name_query = $request->query('name');

        $data = PerlengkapanKebersihan::paginate($value_per_page);

        if ($name_query) {
            $data = PerlengkapanKebersihan::where('name', 'like', '%' . $name_query . '%')->paginate($value_per_page);
        }

        //add query string to all response links
        $data->appends(['value_per_page' => $value_per_page]);
        $data->appends(['name' => $name_query]);

        return response()->json($data);
    }
}
