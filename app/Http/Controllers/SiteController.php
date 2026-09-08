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

    /**
     * Store a newly created site.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'required|string|max:255',
            'expanation_name' => 'nullable|string|max:255',
            'desc' => 'nullable|string|max:255',
            'logo_path' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:10',
        ]);

        $site = Site::create($validated);
        return new SiteResource($site);
    }

    /**
     * Update the specified site.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $site = Site::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'link' => 'sometimes|string|max:255',
            'expanation_name' => 'nullable|string|max:255',
            'desc' => 'nullable|string|max:255',
            'logo_path' => 'nullable|string|max:255',
            'pic' => 'nullable|string|max:10',
        ]);

        $site->update($validated);
        return new SiteResource($site);
    }
}
