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

    /**
     * Wallclock budget for the synchronous fan-out across all channels.
     * Cloudways PHP-FPM max_execution_time is typically 120s; leave headroom
     * so the response can render and the BoostSubmission row saves.
     * Channels exceeding the budget get a deferred-result marker and can be
     * retried later (or moved to a queue worker once Supervisor is configured).
     */
    public const WALLCLOCK_BUDGET_SECONDS = 60;

    public function __construct(
        public IndexNowService $indexNow,
        public WaybackService $wayback,
        public ArchiveTodayService $archiveToday,
        public WebSubService $webSub,
        public GistService $gist,
        public BlueskyService $bluesky,
        public TelegramService $telegram,
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
            'gist' => true,
            'bluesky' => true,
            'telegram' => true,
        ], $channels);

        $this->checkRateLimits($conn, $url);

        $domain = parse_url($url, PHP_URL_HOST);

        $sub = BoostSubmission::create([
            'connection_id' => $conn?->id,
            'url' => $url,
            'domain' => $domain,
        ]);

        $deadline = microtime(true) + self::WALLCLOCK_BUDGET_SECONDS;
        $skipped = ['ok' => false, 'reason' => 'skipped: wallclock budget exhausted, retry later'];
        $run = function (string $col, callable $fn) use (&$sub, $deadline, $skipped) {
            if (microtime(true) >= $deadline) {
                $sub->{$col} = $skipped;
                return;
            }
            try { $sub->{$col} = $fn(); }
            catch (\Throwable $e) { $sub->{$col} = ['ok' => false, 'error' => $e->getMessage()]; }
        };

        // Fast / authoritative channels first so partial completion is still useful.
        if ($channels['indexnow'])     $run('indexnow_result',     fn() => $this->indexNow->submit($url));
        if ($channels['indexing_api']) {
            if ($conn && $conn->access_token) {
                $run('indexing_api_result', fn() => $this->callIndexingApi($conn, $url));
            } else {
                $sub->indexing_api_result = ['ok' => false, 'reason' => 'No Google connection or scope not granted. Re-authenticate to enable.'];
            }
        }
        if ($channels['websub'])       $run('websub_result',       fn() => $this->webSub->ping($url));
        if ($channels['wayback'])      $run('wayback_result',      fn() => $this->wayback->save($url));
        if ($channels['gist'])         $run('gist_result',         fn() => $this->gist->publish($url));
        if ($channels['bluesky'])      $run('bluesky_result',      fn() => $this->bluesky->post($url));
        if ($channels['telegram'])     $run('telegram_result',     fn() => $this->telegram->postToPublicChannel($url));
        // archive.today last — high latency and frequently 429/403 from server IPs.
        if ($channels['archive_today']) $run('archive_today_result', fn() => $this->archiveToday->save($url));

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
