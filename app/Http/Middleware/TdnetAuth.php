<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TdnetAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = env('TDNET_DASHBOARD_PASSWORD');

        if (!$expected) {
            // No password configured: deny all (safe default)
            abort(503, 'TDNET_DASHBOARD_PASSWORD not set in .env');
        }

        if ($request->session()->get('tdnet_auth') === $expected) {
            return $next($request);
        }

        if ($request->isMethod('post') && $request->input('password') === $expected) {
            $request->session()->put('tdnet_auth', $expected);
            return redirect('/tdnet');
        }

        if ($request->isMethod('post') && $request->input('password')) {
            return response()->view('tdnet.login', ['error' => 'Wrong password.'], 401);
        }

        return response()->view('tdnet.login', ['error' => null]);
    }
}
