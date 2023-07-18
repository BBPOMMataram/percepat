<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanListAtk extends Model
{
    use HasFactory;

    public function atk()
    {
        return $this->hasOne(Atk::class, 'id', 'atk_id');
    }

    public function permintaan()
    {
        return $this->hasOne(Permintaan::class, 'id', 'permintaan_id');
    }
}
