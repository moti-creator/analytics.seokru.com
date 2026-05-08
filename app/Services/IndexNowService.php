<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * IndexNow protocol — submit URLs to Bing, Yandex, Yep, Seznam, Naver.
 * Bing index also feeds ChatGPT, Copilot, Brave (Brave supports IndexNow directly).
 *
 * Spec: https://www.indexnow.org/documentation
 */
class IndexNowService
{
    /** Endpoints that accept IndexNow pings. */
    public const ENDPOINTS = [
        // Canonical fan-out: forwards to ALL IndexNow participants + future ones
        'indexnow' => 'https://api.indexnow.org/indexnow',
        // Direct submits (redundant with canonical but ensures delivery)
        'bing' => 'https://www.bing.com/indexnow',
        'yandex' => 'https://yandex.com/indexnow',
        'naver' => 'https://searchadvisor.naver.com/indexnow',
        'seznam' => 'https://search.seznam.cz/indexnow',
        'yep' => 'https://yep.com/indexnow',
    ];

    /**
     * Get-or-create a stable IndexNow key for the given host.
     * Cached forever (file driver) so the same key is reused across submissions.
     */
    public function keyForHost(string $host): string
    {
        $host = strtolower($host);
        return Cache::rememberForever("indexnow_key:{$host}", function () {
            return Str::lower(Str::random(32));
        });
    }

    /**
     * Submit a single URL. The key file MUST be reachable at
     *   https://{host}/{key}.txt   (containing the key as plain text).
     * If not, search engines reject (403).
     *
     * @return array<string,mixed>  Per-engine status.
     */
    public function submit(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return ['error' => 'invalid url', 'url' => $url];
        }

        $key = $this->keyForHost($host);
        $keyLocation = "https://{$host}/{$key}.txt";

        // Verify key file is reachable (best-effort).
        $keyOk = false;
        try {
            $res = Http::timeout(5)->get($keyLocation);
            $keyOk = $res->ok() && trim($res->body()) === $key;
        } catch (\Throwable $e) {
            $keyOk = false;
        }

        $perEngine = [];
        foreach (self::ENDPOINTS as $name => $endpoint) {
            $body = [
                'host' => $host,
                'key' => $key,
                'keyLocation' => $keyLocation,
                'urlList' => [$url],
            ];
            try {
                $r = Http::timeout(8)->withHeaders(['Content-Type' => 'application/json'])
                    ->post($endpoint, $body);
                $perEngine[$name] = [
                    'status' => $r->status(),
                    'ok' => $r->successful(),
                ];
            } catch (\Throwable $e) {
                $perEngine[$name] = ['status' => 0, 'ok' => false, 'error' => $e->getMessage()];
            }
        }

        return [
            'host' => $host,
            'key' => $key,
            'key_location' => $keyLocation,
            'key_file_installed' => $keyOk,
            'engines' => $perEngine,
            'submitted_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Build the instructions a user needs to install the key file on their domain.
     * Used when key_file_installed is false.
     */
    public function installInstructions(string $host): array
    {
        $key = $this->keyForHost($host);
        return [
            'filename' => "{$key}.txt",
            'content' => $key,
            'upload_to' => "https://{$host}/{$key}.txt",
            'verify' => "Visit https://{$host}/{$key}.txt — the page must show only: {$key}",
        ];
    }
}
