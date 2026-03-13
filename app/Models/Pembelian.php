<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// NAMA NYA INI UNTUK PENERIMAAN REAGEN, KALO ATK DAN PERLENGKAPAN NAMANYA PENERIMAAN
class Pembelian extends Model
{
    use HasFactory;
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $casts = [
        'expired' => 'datetime'
    ];

    protected $fillable = [
        'barangs_id',
        'jumlah',
        'vendor',
        'created_at'
    ];

    /**
     * Get the barang associated with the Pembelian
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function barang()
    {
        return $this->hasOne(Barang::class, 'id', 'barangs_id');
    }
}
