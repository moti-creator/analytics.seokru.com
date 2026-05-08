<?php

namespace App\Services;

use App\Models\BoostSubmission;
use App\Models\Connection;

/**
 * Orchestrator: runs all Boost channels for a URL.
 *
 *   1. IndexNow ping — Bing/Yandex/Naver/Seznam/Yep + canonical fanout
 *   2. Google Indexing API — direct crawl nudge
 *   3. Wayback Machine — archive.org snapshot (persistent indexable URL)
 *   4. Archive.today — archive.ph snapshot (alt archive)
 *   5. WebSub — pubsubhubbub feed ping (sites with RSS)
 *   6. (Future) Reddit submission via OAuth
 *
 * Backend rate limits enforced before any submission.
 */
class BoostService
{
    public const MAX_PER_USER_PER_WEEK = 10;
    public const MAX_PER_DOMAIN_PER_DAY = 5;

    public function __construct(
        public IndexNowService $indexNow,
        public WaybackService $wayback,
        public ArchiveTodayService $archiveToday,
        public WebSubService $webSub,
    ) {}

    public function checkRateLimits(?Connection $conn, string $url): void
    {
        $domain = parse_url($url, PHP_URL_HOST);

        if ($conn) {
            $weekCount = BoostSubmission::where('connection_id', $conn->id)
                ->where('created_at', '>=', now()->subWeek())
                ->count();
            if ($weekCount >= self::MAX_PER_USER_PER_WEEK) {
                throw new \RuntimeException("Weekly limit reached (" . self::MAX_PER_USER_PER_WEEK . " URLs / 7 days). Try again later.");
            }
        }

        $domainCount = BoostSubmission::where('domain', $domain)
            ->where('created_at', '>=', now()->subDay())
            ->count();
        if ($domainCount >= self::MAX_PER_DOMAIN_PER_DAY) {
            throw new \RuntimeException("Domain rate limit reached (" . self::MAX_PER_DOMAIN_PER_DAY . " submissions / 24h per domain).");
        }
    }

    /**
     * @param array{indexnow?:bool, indexing_api?:bool, wayback?:bool, archive_today?:bool, websub?:bool} $channels
     */
    public function boost(string $url, ?Connection $conn = null, array $channels = []): BoostSubmission
    {
        $channels = array_merge([
            'indexnow' => true,
            'indexing_api' => true,
            'wayback' => true,
            'archive_today' => true,
            'websub' => true,
        ], $channels);

        $this->checkRateLimits($conn, $url);

        $domain = parse_url($url, PHP_URL_HOST);

        $sub = BoostSubmission::create([
            'connection_id' => $conn?->id,
            'url' => $url,
            'domain' => $domain,
        ]);

        if ($channels['indexnow']) {
            $sub->indexnow_result = $this->indexNow->submit($url);
        }

        if ($channels['indexing_api'] && $conn && $conn->access_token) {
            $sub->indexing_api_result = $this->callIndexingApi($conn, $url);
        } else {
            $sub->indexing_api_result = ['ok' => false, 'reason' => 'No Google connection or scope not granted. Re-authenticate to enable.'];
        }

        if ($channels['wayback']) {
            try { $sub->wayback_result = $this->wayback->save($url); }
            catch (\Throwable $e) { $sub->wayback_result = ['ok' => false, 'error' => $e->getMessage()]; }
        }

        if ($channels['archive_today']) {
            try { $sub->archive_today_result = $this->archiveToday->save($url); }
            catch (\Throwable $e) { $sub->archive_today_result = ['ok' => false, 'error' => $e->getMessage()]; }
        }

        if ($channels['websub']) {
            try { $sub->websub_result = $this->webSub->ping($url); }
            catch (\Throwable $e) { $sub->websub_result = ['ok' => false, 'error' => $e->getMessage()]; }
        }

        $sub->save();
        return $sub;
    }

    protected function callIndexingApi(Connection $conn, string $url): array
    {
        try {
            $g = new GoogleService($conn);
            return $g->indexingApiNotify($url, 'URL_UPDATED');
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function followUpCheck(BoostSubmission $sub, string $slot): void
    {
        if (!$sub->connection || !$sub->connection->gsc_site_url) return;
        if (!in_array($slot, ['inspection_24h', 'inspection_72h', 'inspection_7d'], true)) return;

        $g = new GoogleService($sub->connection);
        $res = $g->urlInspect($sub->connection->gsc_site_url, $sub->url);

        $sub->{$slot} = $res;

        $verdict = data_get($res, 'body.inspectionResult.indexStatusResult.verdict');
        if ($verdict === 'PASS') $sub->indexed = true;
        elseif ($verdict === 'FAIL') $sub->indexed = false;

        if ($slot === 'inspection_7d') $sub->completed_at = now();

        $sub->save();
    }
}
