<?php

namespace App\Http\Controllers;

use App\Models\PenerimaanAtk;
use Illuminate\Http\Request;

class PenerimaanAtkController extends Controller
{
    function index(Request $request)
    {
        $value_per_page_query = $request->query('value_per_page');
        $name_query = $request->query('name');
        $limit_query = $request->query('limit');

        $data = PenerimaanAtk::paginate($value_per_page_query);
        //add query string to all response links
        $data->appends(['value_per_page' => $value_per_page_query]);
        $data->appends(['name' => $name_query]);

        //FOR REQUEST IN DASHBOARD FRONTEND, IT HAS LIMIT
        if ($limit_query) {
            // $data = PenerimaanAtk::with('atk')->limit($limit_query)->get();
            // if ($name_query) {
            $data = PenerimaanAtk::whereHas(
                'atk',
                function ($query) use ($name_query) {
                    $query->where('name', 'like', '%' . $name_query . '%');
                }
            )->with('atk')->limit($limit_query)->get();
            // }
        }

        return response()->json($data);
    }
}
