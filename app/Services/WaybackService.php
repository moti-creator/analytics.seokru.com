<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Wayback Machine — web.archive.org/save/{url} creates a persistent snapshot.
 * The snapshot URL is publicly indexable, crawled by Google + Bing, and cited
 * by some LLM training pipelines. Free, no auth.
 */
class WaybackService
{
    public function save(string $url): array
    {
        $saveUrl = 'https://web.archive.org/save/' . $url;
        try {
            $r = Http::timeout(60)
                ->withHeaders(['User-Agent' => 'SEOKRU-Analytics/1.0 (+https://analytics.seokru.com)'])
                ->get($saveUrl);

            // The response Location header / final URL contains the snapshot path
            $finalUrl = $r->effectiveUri()?->__toString() ?? $saveUrl;
            $snapshotUrl = null;

            // Extract /web/{timestamp}/{url} pattern
            if (preg_match('#https?://web\.archive\.org/web/(\d+)/(.+)#', $finalUrl, $m)) {
                $snapshotUrl = "https://web.archive.org/web/{$m[1]}/{$m[2]}";
            }

            return [
                'ok' => $r->successful(),
                'status' => $r->status(),
                'snapshot_url' => $snapshotUrl,
                'submitted_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
