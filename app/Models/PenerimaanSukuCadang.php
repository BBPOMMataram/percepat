<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanSukuCadang extends Model
{
    use HasFactory;

    protected $fillable = ['suku_cadang_id', 'jumlah'];

    public function sukuCadang()
    {
        return $this->belongsTo(SukuCadang::class);
    }
}
