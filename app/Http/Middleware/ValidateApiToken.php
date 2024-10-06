<?php

namespace App\Http\Middleware;

use App\Settings\TcxApiSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(TcxApiSettings::class);

        $header = $request->header('Authorization');

        if (!$header) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($header !== $settings->api_key) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
