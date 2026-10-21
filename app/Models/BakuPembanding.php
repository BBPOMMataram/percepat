<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class BakuPembanding extends Model
{
    use HasFactory;

    protected $table = 'baku_pembandings';

    protected $casts = [
        'expired' => 'datetime'
    ];

    protected $fillable = [
        'code',
        'kode_baru',
        'name',
        'letak',
        'satuan',
        'expired',
        'stock',
        'msds',
    ];

    function getMsdsAttribute($value)
    {
        return $value != null && $value != '-' ?? Storage::url($value);
    }
}
