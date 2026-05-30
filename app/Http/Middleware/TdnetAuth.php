<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TdnetAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->session()->get('tdnet_email');
        if ($email && self::isAllowed($email)) {
            return $next($request);
        }

        $flashError = $request->session()->pull('tdnet_login_error');
        return response()->view('tdnet.login', ['error' => $flashError]);
    }

    public static function isAllowed(string $email): bool
    {
        $list = array_filter(array_map('trim', explode(',', strtolower((string) env('TDNET_ALLOWED_EMAILS', '')))));
        return in_array(strtolower($email), $list, true);
    }
}
