<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ApiUser extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    function getPhotoAttribute($value) {
        return $value ? Storage::url($value) : null;
    }

    function getSignatureAttribute($value) {
        return $value ? Storage::url($value) : null;
    }
}
