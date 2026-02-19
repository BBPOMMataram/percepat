<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanListPerlengkapanKebersihan extends Model
{
    use HasFactory;

    public function barang()
    {
        return $this->hasOne(PerlengkapanKebersihan::class, 'id', 'perlengkapan_kebersihan_id');
    }

    public function permintaan()
    {
        return $this->hasOne(Permintaan::class, 'id', 'permintaan_id');
    }
}
