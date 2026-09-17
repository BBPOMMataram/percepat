<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanListSukuCadang extends Model
{
    use HasFactory;

    protected $table = 'permintaan_list_suku_cadangs';

    protected $fillable = ['permintaan_id', 'suku_cadang_id', 'jumlahpermintaan', 'jumlahrealisasi', 'keterangan'];

    public function sukuCadang()
    {
        return $this->belongsTo(SukuCadang::class, 'suku_cadang_id', 'id');
    }

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }
}
