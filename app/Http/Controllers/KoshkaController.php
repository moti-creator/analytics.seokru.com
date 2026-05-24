<?php

namespace App\Http\Controllers;

use App\Http\Middleware\KoshkaAuth;
use App\Services\MetaAgentService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class KoshkaController extends Controller
{
    public function authRedirect()
    {
        return Socialite::driver('google')
            ->redirectUrl(url('/koshka/auth/google/callback'))
            ->scopes(['email', 'profile'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function authCallback(Request $request)
    {
        try {
            $g = Socialite::driver('google')
                ->redirectUrl(url('/koshka/auth/google/callback'))
                ->user();
        } catch (\Throwable $e) {
            $request->session()->flash('koshka_login_error', 'Google sign-in failed: ' . $e->getMessage());
            return redirect('/koshka');
        }

        $email = strtolower((string) $g->getEmail());
        if (!$email || !KoshkaAuth::isAllowed($email)) {
            $request->session()->flash('koshka_login_error', "המייל {$email} אינו מורשה.");
            return redirect('/koshka');
        }

        $request->session()->put('koshka_email', $email);
        $request->session()->put('koshka_name', $g->getName() ?: $email);
        return redirect('/koshka');
    }

    public function index(Request $request)
    {
        return view('koshka.index', [
            'userName' => $request->session()->get('koshka_name', ''),
            'userEmail' => $request->session()->get('koshka_email', ''),
        ]);
    }

    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:8000',
        ]);

        // Convert flat message array to Claude format
        $messages = array_map(fn($m) => [
            'role' => $m['role'],
            'content' => $m['content'],
        ], $request->input('messages'));

        $agent = new MetaAgentService();
        $result = $agent->chat($messages);

        return response()->json([
            'reply' => $result['reply'],
            'tool_calls' => $result['tool_calls'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['koshka_email', 'koshka_name']);
        return redirect('/koshka');
    }
}
