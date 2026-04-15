<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Connection;

class AuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/analytics.readonly',
                'https://www.googleapis.com/auth/webmasters.readonly',
            ])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        $g = Socialite::driver('google')->user();

        $conn = Connection::updateOrCreate(
            ['google_user_id' => $g->getId()],
            [
                'email' => $g->getEmail(),
                'access_token' => $g->token,
                'refresh_token' => $g->refreshToken ?? null,
                'expires_at' => now()->addSeconds($g->expiresIn ?? 3600),
            ]
        );

        session(['connection_id' => $conn->id]);
        return redirect()->route('connect');
    }
}
