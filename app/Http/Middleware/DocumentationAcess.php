<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DocumentationAcess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()->canViewDocumentation()) {
            abort(401, 'You are not authorised to view this resource');
        }

        return $next($request);
    }
}
