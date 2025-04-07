<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventBackMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Strict caching prevention for all pages
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private, max-age=0, no-transform');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Additional security headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Different caching for authenticated and guest routes
        if (Auth::check()) {
            // For authenticated routes
            $response->headers->set('Cache-Control', 'private, no-cache, no-store, must-revalidate, max-age=0, no-transform');
        } else {
            // For guest routes
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0, no-transform');
        }

        return $response;
    }
}
