<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiBidang extends Model
{
    use HasFactory;

    protected $table = 'bidangs';

    /**
     * Get the user associated with the ApiBidang
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function kabidFrom()
    {
        return $this->hasOne(User::class,'id', 'kabid');
    }
}
