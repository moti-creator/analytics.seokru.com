<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\RememberToken;

/**
 * If user has no active session but carries a "remember_connection" cookie,
 * restore the session via the rotating token table. The cookie holds a random
 * 48-char string; the DB only stores its sha256 hash. Token rotates on each
 * successful restore so a stolen cookie has a single-use lifespan.
 */
class RestoreConnection
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('connection_id')) {
            $raw = $request->cookie('remember_connection');
            if ($raw) {
                $row = RememberToken::where('token_hash', RememberToken::hash($raw))
                    ->where('expires_at', '>', now())
                    ->first();
                if ($row) {
                    $request->session()->put('connection_id', $row->connection_id);

                    // Rotate so the previous cookie value can't be replayed.
                    $newRaw = Str::random(48);
                    $row->update([
                        'token_hash' => RememberToken::hash($newRaw),
                        'last_used_at' => now(),
                    ]);
                    Cookie::queue('remember_connection', $newRaw, 60 * 24 * 30);
                } else {
                    // Cookie present but invalid/expired — clear it.
                    Cookie::queue(Cookie::forget('remember_connection'));
                }
            }
        }
        return $next($request);
    }
}
