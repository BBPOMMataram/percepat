<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanAtk extends Model
{
    use HasFactory;

    public function atk()
    {
        return $this->belongsTo(Atk::class);
    }
}
