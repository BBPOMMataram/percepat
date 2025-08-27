<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/login',
        '/api/spp' // Khusus untuk endpoint survey pelayanan publik di bypass csrf karena dianggap stateful request karena file html nya langsung di folder public
    ];
}
