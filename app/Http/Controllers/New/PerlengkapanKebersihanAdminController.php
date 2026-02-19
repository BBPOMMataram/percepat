<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Models\PerlengkapanKebersihan;
use Illuminate\Http\Request;

class PerlengkapanKebersihanAdminController extends Controller
{
    // UNTUK crud ADMIN PERCEPAT
    function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $name_query = $request->query('name');

        $query = PerlengkapanKebersihan::where('name', 'like', '%' . $name_query . '%');

        $data = $query->paginate($perPage, ['*'], 'page', $page)->appends([
            // 'kode_or_name' => $kode_or_name
        ]);

        return response()->json($data);
    }
}
