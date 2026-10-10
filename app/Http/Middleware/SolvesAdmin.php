<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Restricts a DISI-Solves route to users with the admin role. */
class SolvesAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(auth('solves')->user()?->role === 'admin', 403, 'Admins only.');

        return $next($request);
    }
}
