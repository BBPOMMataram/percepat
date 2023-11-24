<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LaporanPermintaanReagenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Eager loading untuk memuat relasi (jika tidak ada di "with" query) 
        // case disini sub relasi nya (bidang) dan sub nya lagi (user/kabid) tidak dibuat di query
        $this->load('permintaan.bidang.user', 'permintaan.status');
        return parent::toArray($request);
    }
}
