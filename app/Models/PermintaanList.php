<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanList extends Model
{
    use HasFactory;

    /**
     * Get the barang associated with the PermintaanList
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function barang()
    {
        return $this->hasOne(Barang::class, 'id', 'barang_id');
    }
}
