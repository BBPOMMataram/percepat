<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpnameBeritaAcara extends Model
{
    use HasFactory;

    protected $table = 'stok_opname_berita_acaras';

    protected $fillable = [
        'judul',
        'nomor_ba',
        'tanggal_ba',
        'file_name',
        'file_path',
        'file_size',
        'uploaded_by',
    ];

    protected $casts = [
        'tanggal_ba' => 'date:Y-m-d',
        'file_size' => 'integer',
    ];
}

