<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBakuPembanding extends Model
{
    use HasFactory;
    protected $dateFormat = 'Y-m-d H:i:s';

    protected $casts = [
        'expired' => 'datetime'
    ];

    protected $fillable = [
        'baku_pembanding_id',
        'jumlah',
        'vendor',
        'created_at',
        'expired',
    ];

    public function baku_pembanding()
    {
        return $this->hasOne(BakuPembanding::class, 'id', 'baku_pembanding_id');
    }

    public function barang()
    {
        return $this->hasOne(BakuPembanding::class, 'id', 'baku_pembanding_id');
    }
}
