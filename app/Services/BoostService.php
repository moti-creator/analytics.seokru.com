<?php

namespace App\Services;

use App\Models\BoostSubmission;
use App\Models\Connection;

/**
 * Orchestrator: runs all Boost channels for a URL.
 *
 *   1. IndexNow ping (Bing/Yandex/Yep/Brave -> ChatGPT/Claude/Copilot)
 *   2. Google Indexing API (if user has GSC access + scope granted)
 *   3. llms.txt generator (returns content, user installs)
 *   4. (Future) Reddit submission via OAuth
 *
 * Backend rate limits enforced before any submission.
 */
class BoostService
{
    public const MAX_PER_USER_PER_WEEK = 10;
    public const MAX_PER_DOMAIN_PER_DAY = 5;

    public function __construct(
        public IndexNowService $indexNow,
        public LlmsTxtService $llmsTxt,
    ) {}

    /**
     * Validate rate limits. Throws \RuntimeException on violation.
     */
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
     * Run all channels and persist a BoostSubmission record.
     *
     * @param array{indexnow?:bool, indexing_api?:bool, llms_txt?:bool} $channels
     */
    public function boost(string $url, ?Connection $conn = null, array $channels = []): BoostSubmission
    {
        $channels = array_merge([
            'indexnow' => true,
            'indexing_api' => true,
            'llms_txt' => true,
        ], $channels);

        $this->checkRateLimits($conn, $url);

        $domain = parse_url($url, PHP_URL_HOST);

        $sub = BoostSubmission::create([
            'connection_id' => $conn?->id,
            'url' => $url,
            'domain' => $domain,
        ]);

        // 1. IndexNow
        if ($channels['indexnow']) {
            $sub->indexnow_result = $this->indexNow->submit($url);
        }

        // 2. Google Indexing API (only if user has GSC connection with property covering this URL)
        if ($channels['indexing_api'] && $conn && $conn->access_token) {
            $sub->indexing_api_result = $this->callIndexingApi($conn, $url);
        } else {
            $sub->indexing_api_result = ['ok' => false, 'reason' => 'No Google connection or scope not granted. Re-authenticate to enable.'];
        }

        // 3. llms.txt — generate content for user to install (or auto-install if we have access)
        if ($channels['llms_txt']) {
            try {
                $sub->llms_txt_result = $this->llmsTxt->build($domain);
            } catch (\Throwable $e) {
                $sub->llms_txt_result = ['ok' => false, 'error' => $e->getMessage()];
            }
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

    /**
     * Run a URL Inspection check and persist into the right slot.
     * Called by the follow-up cron at 24h/72h/7d.
     */
    public function followUpCheck(BoostSubmission $sub, string $slot): void
    {
        if (!$sub->connection || !$sub->connection->gsc_site_url) return;
        if (!in_array($slot, ['inspection_24h', 'inspection_72h', 'inspection_7d'], true)) return;

        $g = new GoogleService($sub->connection);
        $res = $g->urlInspect($sub->connection->gsc_site_url, $sub->url);

        $sub->{$slot} = $res;

        // Determine indexed status from latest available result
        $verdict = data_get($res, 'body.inspectionResult.indexStatusResult.verdict');
        if ($verdict === 'PASS') {
            $sub->indexed = true;
        } elseif ($verdict === 'FAIL') {
            $sub->indexed = false;
        }

        if ($slot === 'inspection_7d') {
            $sub->completed_at = now();
        }

        $sub->save();
    }
}
