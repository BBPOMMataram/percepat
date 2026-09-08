<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Site extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'link', 'expanation_name', 'desc', 'logo_path', 'pic', 'clicks'];

    // accessor
    public function getLogoPathAttribute($value)
    {
        return Storage::url($value);
    }
}
