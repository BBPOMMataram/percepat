<?php

namespace App\Http\Controllers;

use App\Http\Resources\SiteResource;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the sites.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSites()
    {
        $sites = Site::all();
        return SiteResource::collection($sites);
    }
}
