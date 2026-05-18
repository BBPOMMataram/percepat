<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanPerlengkapanKebersihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'perlengkapan_kebersihan_id',
        'jumlah',
        'vendor',
        'created_at'
    ];

    public function barang()
    {
        return $this->hasOne(PerlengkapanKebersihan::class, 'id', 'perlengkapan_kebersihan_id');
    }
}
