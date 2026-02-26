<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permintaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status_id',
        'kasubbagumum_id',
        'penyerah_id',
    ];

    protected $casts = [
        'tgl_permintaan' => 'datetime',
        'tgl_penyerahan' => 'datetime',
    ];

    /**
     * Get the user associated with the Permintaan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    // public function kabid()
    // {
    //     return $this->hasOne(User::class, 'id', 'bidang_id');
    // }

    public function peminta()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function status()
    {
        return $this->hasOne(Status::class, 'id', 'status_id');
    }

    // ga perlu karena udah ada permintaan list table yang berisi data barang
    // public function barang()
    // {
    //     return $this->hasOne(Status::class, 'id', 'barang_id');
    // }

    public function bidang()
    {
        return $this->hasOne(Bidang::class, 'id', 'bidang_id');
    }

    public function katim()
    {
        return $this->hasOne(User::class, 'id', 'katim_selected');
    }
}
