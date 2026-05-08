<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Archive.today (archive.ph / archive.is) — alternative web archive that
 * sometimes captures pages Wayback can't (heavy JS, paywalls). Snapshots are
 * publicly indexable and persistent.
 *
 * No official API — uses the public submission form.
 */
class ArchiveTodayService
{
    public function save(string $url): array
    {
        try {
            $r = Http::timeout(45)
                ->asForm()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; SEOKRU-Analytics/1.0)',
                    'Referer' => 'https://archive.ph/',
                ])
                ->post('https://archive.ph/submit/', [
                    'url' => $url,
                    'anyway' => '1',
                ]);

            // Archive.ph returns 200 or 302 on success, with snapshot URL in Refresh / Location header
            $snapshotUrl = $r->header('Refresh');
            if (preg_match('#url=(.+)#i', (string)$snapshotUrl, $m)) {
                $snapshotUrl = trim($m[1]);
            }
            if (!$snapshotUrl) {
                $snapshotUrl = $r->header('Location');
            }

            return [
                'ok' => $r->successful() || $r->status() === 302,
                'status' => $r->status(),
                'snapshot_url' => $snapshotUrl ?: null,
                'submitted_at' => now()->toIso8601String(),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
