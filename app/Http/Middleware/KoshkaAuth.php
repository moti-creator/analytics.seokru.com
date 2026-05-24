<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KoshkaAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->session()->get('koshka_email');
        if ($email && self::isAllowed($email)) {
            return $next($request);
        }

        $flashError = $request->session()->pull('koshka_login_error');
        return response()->view('koshka.login', ['error' => $flashError]);
    }

    public static function isAllowed(string $email): bool
    {
        $list = array_filter(array_map('trim', explode(',', strtolower((string) env('KOSHKA_ALLOWED_EMAILS', '')))));
        return in_array(strtolower($email), $list, true);
    }
}
