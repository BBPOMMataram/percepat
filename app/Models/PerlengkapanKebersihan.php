<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerlengkapanKebersihan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'satuan',
        'stock',
        'description',
    ];
}
