<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $casts = [
        'tgl_permintaan' => 'datetime',
        'tgl_penyerahan' => 'datetime',
    ];

    /**
     * Get the user associated with the Permintaan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function kabid()
    {
        return $this->hasOne(User::class, 'id', 'kabid_id');
    }

    public function peminta()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    
    public function status()
    {
        return $this->hasOne(Status::class, 'id', 'status_id');
    }

    public function barang()
    {
        return $this->hasOne(Status::class, 'id', 'barang_id');
    }


    
}
