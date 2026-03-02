<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListPermintaanAtkResource;
use App\Models\ApiUser;
use App\Models\Permintaan;
use App\Models\PermintaanListAtk;
use Barryvdh\DomPDF\Facade as PDF;

class PermintaanListAtkController extends Controller
{
    public function list_permintaan_atk($permintaanId)
    {
        $data = PermintaanListAtk::with('atk', 'permintaan')
            ->where('permintaan_id', $permintaanId)
            ->get();

        return new ListPermintaanAtkResource($data);
    }

    function download_permintaan_atk($permintaanId)
    {
        $datapermintaan = Permintaan::find($permintaanId);
        $datapermintaanlist = PermintaanListAtk::where('permintaan_id', $permintaanId)->get();
        $penyerah = $datapermintaan->penyerah_id ? ApiUser::find($datapermintaan->penyerah_id) : null;
        $kasub = ApiUser::where('position', 'kasubbagumum')->first();
        $pemohon = ApiUser::find($datapermintaan->created_by);
        $kabid = ApiUser::find($datapermintaan->katim_selected); // ga pake external_user_id karena saat membuat permintaan baru sudah menyimpan id user
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
        $pdf = PDF::loadView('pdf/permintaan-atk', compact(
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
