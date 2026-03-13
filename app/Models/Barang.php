<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Barang extends Model
{
    use HasFactory;

    protected $casts = [
        'expired' => 'datetime'
    ];

    protected $fillable = [
        'stock',
        'name',
        'satuan',
        'expired',
    ];

    function getMsdsAttribute($value)
    {
        return $value != null && $value != '-' ?? Storage::url($value);
    }
}
