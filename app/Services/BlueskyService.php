<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Bluesky AT Protocol post creator.
 * Posts on Bluesky are indexed by Google + Bing within hours.
 *
 * Auth: handle + app password (user creates app password at
 * https://bsky.app/settings/app-passwords). Set env:
 *   BLUESKY_HANDLE=...
 *   BLUESKY_APP_PASSWORD=...
 */
class BlueskyService
{
    public const PDS = 'https://bsky.social';

    /** Authenticate and cache JWT for ~50 min. */
    protected function jwt(): ?string
    {
        $handle = env('BLUESKY_HANDLE');
        $pw = env('BLUESKY_APP_PASSWORD');
        if (!$handle || !$pw) return null;

        return Cache::remember("bsky_jwt:{$handle}", 50 * 60, function () use ($handle, $pw) {
            $r = Http::timeout(10)->post(self::PDS . '/xrpc/com.atproto.server.createSession', [
                'identifier' => $handle,
                'password' => $pw,
            ]);
            return $r->successful() ? $r->json('accessJwt') : null;
        });
    }

    public function post(string $url, ?string $text = null): array
    {
        $jwt = $this->jwt();
        if (!$jwt) {
            return ['ok' => false, 'reason' => 'Bluesky credentials not configured (BLUESKY_HANDLE, BLUESKY_APP_PASSWORD)'];
        }

        $domain = parse_url($url, PHP_URL_HOST);
        $text = $text ?: "Worth a read: {$url}";
        // Bluesky max 300 chars
        if (mb_strlen($text) > 300) $text = mb_substr($text, 0, 297) . '...';

        // Build facets so the URL is a real link, not just text
        $byteStart = strpos($text, $url);
        $byteEnd = $byteStart === false ? null : $byteStart + strlen($url);
        $facets = [];
        if ($byteStart !== false) {
            $facets[] = [
                'index' => ['byteStart' => $byteStart, 'byteEnd' => $byteEnd],
                'features' => [['$type' => 'app.bsky.richtext.facet#link', 'uri' => $url]],
            ];
        }

        try {
            $r = Http::timeout(15)
                ->withToken($jwt)
                ->post(self::PDS . '/xrpc/com.atproto.repo.createRecord', [
                    'repo' => env('BLUESKY_HANDLE'),
                    'collection' => 'app.bsky.feed.post',
                    'record' => [
                        'text' => $text,
                        'facets' => $facets,
                        'createdAt' => now()->toIso8601String(),
                        '$type' => 'app.bsky.feed.post',
                    ],
                ]);

            $uri = $r->json('uri'); // at://did:plc:.../app.bsky.feed.post/xyz
            $post_url = null;
            if ($uri && preg_match('#at://([^/]+)/[^/]+/(.+)#', $uri, $m)) {
                $post_url = "https://bsky.app/profile/{$m[1]}/post/{$m[2]}";
            }

            return [
                'ok' => $r->successful(),
                'status' => $r->status(),
                'post_url' => $post_url,
                'uri' => $uri,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
