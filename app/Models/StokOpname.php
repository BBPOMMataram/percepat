<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opnames';

    protected $fillable = [
        'barang_type',
        'barang_id',
        'kode_barang',
        'nama_barang',
        'satuan',
        'lokasi',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
        'petugas_name',
        'petugas_external_id',
        'tanggal_opname',
    ];

    protected $casts = [
        'tanggal_opname' => 'datetime',
        'stok_sistem' => 'integer',
        'stok_fisik' => 'integer',
        'selisih' => 'integer',
    ];
}
