<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangLab extends Model
{
    use HasFactory;


    /**
     * Get the barang associated with the BarangLab
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function barang()
    {
        return $this->hasOne(Barang::class);
    }

    /**
     * Get the bidang associated with the BarangLab
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bidang()
    {
        return $this->hasOne(Bidang::class);
    }
}
