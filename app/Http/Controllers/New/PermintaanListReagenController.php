<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListPermintaanAtkResource;
use App\Models\ApiUser;
use App\Models\Permintaan;
use App\Models\PermintaanList;
use Barryvdh\DomPDF\Facade as PDF;

class PermintaanListReagenController extends Controller
{
    public function list_permintaan_reagen($permintaanId)
    {
        $data = PermintaanList::with('barang', 'permintaan')
            ->where('permintaan_id', $permintaanId)
            ->get();

        return new ListPermintaanAtkResource($data);
    }

    function download_permintaan_reagen($permintaanId)
    {
        $datapermintaan = Permintaan::with('bidang')->find($permintaanId);
        // return $datapermintaan->bidang;
        $datapermintaanlist = PermintaanList::where('permintaan_id', $permintaanId)->get();
        $penyerah = $datapermintaan->penyerah_id ? ApiUser::find($datapermintaan->penyerah_id) : null;
        $kasub = ApiUser::where('position', 'kasubbagumum')->first();
        $pemohon = ApiUser::find($datapermintaan->created_by);
        $kabid = ApiUser::find($datapermintaan->katim_selected ?? $datapermintaan->kabid_id);
        function pdfSignature($model)
        {
            $signature = $model?->getRawOriginal('signature');

            if ($signature && file_exists(public_path('storage/' . $signature))) {
                return public_path('storage/' . $signature);
            }

            return public_path('vendor/assets/images/image-not-found.webp');
        }

        $penyerahSignature = pdfSignature($penyerah);
        $kasubSignature    = pdfSignature($kasub);
        $pemohonSignature  = pdfSignature($pemohon);
        $kabidSignature    = pdfSignature($kabid);

        $logobpom = 'storage/bpomri.jpg';
        // return $datapermintaan;
        $pdf = PDF::loadView('pdf/permintaan', compact(
            'datapermintaan',
            'datapermintaanlist',
            'penyerah',
            'kasub',
            'pemohon',
            'kabid',
            'penyerahSignature',
            'kasubSignature',
            'pemohonSignature',
            'kabidSignature',
            'logobpom',
        ));

        return $pdf->download();
    }
}
