<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOcrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthorized: OCR admin access required.'
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Unauthorized: OCR admin access required.');
        }

        return $next($request);
    }
}