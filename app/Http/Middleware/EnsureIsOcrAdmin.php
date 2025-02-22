<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsOcrAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->canReviewOcr()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Vous n\'avez pas la permission d\'accéder à l\'administration OCR.'
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', 'Vous n\'avez pas la permission d\'accéder à l\'administration OCR.');
        }

        return $next($request);
    }
}