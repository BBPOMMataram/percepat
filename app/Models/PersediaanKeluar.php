<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersediaanKeluar extends Model
{
    use HasFactory;

    protected $table = 'persediaan_keluars';

    protected $fillable = [
        'barang_type',
        'barang_id',
        'kode_barang',
        'nama_barang',
        'satuan',
        'lokasi',
        'kategori_keluar',
        'jumlah',
        'stok_awal',
        'stok_sisa',
        'keterangan',
        'petugas_name',
        'tanggal_keluar',
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
        'jumlah' => 'integer',
        'stok_awal' => 'integer',
        'stok_sisa' => 'integer',
    ];
}

