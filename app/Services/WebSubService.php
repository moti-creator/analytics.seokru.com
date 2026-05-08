<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * WebSub (formerly PubSubHubbub) — push protocol for feed updates.
 * Google's hub at pubsubhubbub.appspot.com is still active. If the site has
 * an RSS/Atom feed, pinging the hub triggers Google to re-fetch the feed,
 * which surfaces new content faster (especially for blogs).
 *
 * Discovery: tries /feed, /rss, /atom.xml, /rss.xml, /sitemap.xml as feed candidates.
 * If none reachable, returns ok=false with reason.
 */
class WebSubService
{
    public const HUBS = [
        'google' => 'https://pubsubhubbub.appspot.com/',
        'superfeedr' => 'https://pubsubhubbub.superfeedr.com/',
    ];

    public function ping(string $url): array
    {
        $domain = parse_url($url, PHP_URL_HOST);
        if (!$domain) {
            return ['ok' => false, 'reason' => 'invalid url'];
        }

        $feed = $this->discoverFeed("https://{$domain}");
        if (!$feed) {
            return ['ok' => false, 'reason' => 'No RSS/Atom feed found at common paths.'];
        }

        $perHub = [];
        foreach (self::HUBS as $name => $hub) {
            try {
                $r = Http::timeout(15)->asForm()->post($hub, [
                    'hub.mode' => 'publish',
                    'hub.url' => $feed,
                ]);
                $perHub[$name] = ['status' => $r->status(), 'ok' => $r->successful()];
            } catch (\Throwable $e) {
                $perHub[$name] = ['status' => 0, 'ok' => false, 'error' => $e->getMessage()];
            }
        }

        return [
            'ok' => collect($perHub)->contains(fn($h) => $h['ok'] ?? false),
            'feed_url' => $feed,
            'hubs' => $perHub,
        ];
    }

    protected function discoverFeed(string $base): ?string
    {
        // Try common paths
        $candidates = ['/feed', '/feed/', '/rss', '/rss.xml', '/atom.xml', '/feed.xml', '/index.xml'];
        foreach ($candidates as $path) {
            $full = rtrim($base, '/') . $path;
            try {
                $r = Http::timeout(6)->head($full);
                if ($r->successful() && str_contains(strtolower($r->header('Content-Type', '')), 'xml')) {
                    return $full;
                }
            } catch (\Throwable $e) {}
        }

        // Try discovery via <link rel="alternate" type="application/rss+xml" href="...">
        try {
            $html = Http::timeout(8)->get($base)->body();
            if (preg_match('#<link[^>]+type=["\']application/(?:rss|atom)\+xml["\'][^>]+href=["\']([^"\']+)["\']#i', $html, $m)) {
                $href = $m[1];
                if (!str_starts_with($href, 'http')) {
                    $href = rtrim($base, '/') . '/' . ltrim($href, '/');
                }
                return $href;
            }
        } catch (\Throwable $e) {}

        return null;
    }
}
