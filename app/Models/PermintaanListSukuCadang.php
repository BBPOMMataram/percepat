<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanListSukuCadang extends Model
{
    use HasFactory;

    protected $fillable = ['permintaan_id', 'suku_cadang_id', 'jumlah'];

    public function sukuCadang()
    {
        return $this->belongsTo(SukuCadang::class);
    }

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }
}
