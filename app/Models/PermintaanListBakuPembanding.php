<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanListBakuPembanding extends Model
{
    use HasFactory;

    protected $fillable = [
        'permintaan_id',
        'baku_pembanding_id',
        'jumlahpermintaan',
        'jumlahrealisasi',
        'keterangan'
    ];

    public function baku_pembanding()
    {
        return $this->hasOne(BakuPembanding::class, 'id', 'baku_pembanding_id');
    }

    public function barang()
    {
        return $this->hasOne(BakuPembanding::class, 'id', 'baku_pembanding_id');
    }

    public function permintaan()
    {
        return $this->hasOne(Permintaan::class, 'id', 'permintaan_id');
    }
}
