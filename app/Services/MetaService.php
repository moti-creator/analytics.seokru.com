<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaService
{
    protected const GRAPH = 'https://graph.facebook.com/v21.0';

    public string $token;
    public string $accountId;

    public function __construct()
    {
        $this->token = (string) env('META_ACCESS_TOKEN', '');
        $this->accountId = (string) env('META_AD_ACCOUNT_ID', '');
    }

    public function configured(): bool
    {
        return $this->token && $this->accountId;
    }

    // --- READS ---

    /** Today's spend, leads, clicks. */
    public function todayStats(): array
    {
        return Cache::remember("koshka:today:{$this->accountId}", 60, function () {
            $resp = $this->get("/{$this->accountId}/insights", [
                'fields' => 'spend,clicks,impressions,actions',
                'date_preset' => 'today',
            ]);
            $row = $resp['data'][0] ?? [];
            return [
                'spend' => (float)($row['spend'] ?? 0),
                'clicks' => (int)($row['clicks'] ?? 0),
                'impressions' => (int)($row['impressions'] ?? 0),
                'leads' => $this->extractLeads($row['actions'] ?? []),
            ];
        });
    }

    /** Month-to-date spend + average daily pace. */
    public function monthStats(): array
    {
        return Cache::remember("koshka:month:{$this->accountId}", 600, function () {
            $start = now()->startOfMonth()->toDateString();
            $end = now()->toDateString();
            $row = $this->fetchPeriod($start, $end);
            $daysElapsed = max(1, now()->day);
            $daysInMonth = now()->daysInMonth;
            $pace = ($row['spend'] / $daysElapsed) * $daysInMonth;
            return array_merge($row, ['days_elapsed' => $daysElapsed, 'projected' => round($pace)]);
        });
    }

    /** Week-over-week comparison (this week vs previous week). */
    public function weekComparison(): array
    {
        return Cache::remember("koshka:weekcompare:{$this->accountId}", 180, function () {
            $today = now();
            $thisStart = $today->copy()->subDays(6)->toDateString();
            $thisEnd = $today->toDateString();
            $lastStart = $today->copy()->subDays(13)->toDateString();
            $lastEnd = $today->copy()->subDays(7)->toDateString();

            $this_ = $this->fetchPeriod($thisStart, $thisEnd);
            $last = $this->fetchPeriod($lastStart, $lastEnd);

            return [
                'this' => $this_,
                'last' => $last,
                'delta' => [
                    'spend' => $this->pctDelta($last['spend'], $this_['spend']),
                    'leads' => $this->pctDelta($last['leads'], $this_['leads']),
                    'ctr' => $this->pctDelta($last['ctr'], $this_['ctr']),
                ],
            ];
        });
    }

    protected function fetchPeriod(string $start, string $end): array
    {
        $resp = $this->get("/{$this->accountId}/insights", [
            'fields' => 'spend,clicks,impressions,ctr,actions',
            'time_range' => json_encode(['since' => $start, 'until' => $end]),
        ]);
        $row = $resp['data'][0] ?? [];
        return [
            'spend' => (float)($row['spend'] ?? 0),
            'clicks' => (int)($row['clicks'] ?? 0),
            'impressions' => (int)($row['impressions'] ?? 0),
            'ctr' => (float)($row['ctr'] ?? 0),
            'leads' => $this->extractLeads($row['actions'] ?? []),
        ];
    }

    /** All campaigns with last-7d insights joined. */
    public function campaigns(): array
    {
        return Cache::remember("koshka:campaigns:{$this->accountId}", 120, function () {
            $campaigns = $this->get("/{$this->accountId}/campaigns", [
                'fields' => 'id,name,status,effective_status,objective,daily_budget,lifetime_budget,created_time',
                'limit' => 100,
            ]);

            $insights = $this->get("/{$this->accountId}/insights", [
                'fields' => 'campaign_id,spend,clicks,ctr,impressions,actions',
                'date_preset' => 'last_7d',
                'level' => 'campaign',
                'limit' => 200,
            ]);

            $byId = [];
            foreach (($insights['data'] ?? []) as $row) {
                $byId[$row['campaign_id']] = [
                    'spend' => (float)($row['spend'] ?? 0),
                    'clicks' => (int)($row['clicks'] ?? 0),
                    'ctr' => (float)($row['ctr'] ?? 0),
                    'impressions' => (int)($row['impressions'] ?? 0),
                    'leads' => $this->extractLeads($row['actions'] ?? []),
                ];
            }

            $out = [];
            foreach (($campaigns['data'] ?? []) as $c) {
                $stats = $byId[$c['id']] ?? ['spend' => 0, 'clicks' => 0, 'ctr' => 0, 'impressions' => 0, 'leads' => 0];
                $cpl = $stats['leads'] > 0 ? round($stats['spend'] / $stats['leads'], 2) : null;
                $out[] = array_merge($c, ['stats' => $stats, 'cpl' => $cpl]);
            }

            // Sort: ACTIVE first, then by spend desc
            usort($out, function ($a, $b) {
                $aActive = $a['status'] === 'ACTIVE' ? 0 : 1;
                $bActive = $b['status'] === 'ACTIVE' ? 0 : 1;
                if ($aActive !== $bActive) return $aActive - $bActive;
                return ($b['stats']['spend'] ?? 0) <=> ($a['stats']['spend'] ?? 0);
            });

            return $out;
        });
    }

    /** Sum daily budgets across all truly-active adsets in the account. */
    public function activeDailyBudget(): float
    {
        return Cache::remember("koshka:activedaily:{$this->accountId}", 300, function () {
            $resp = $this->get("/{$this->accountId}/adsets", [
                'fields' => 'daily_budget,effective_status',
                'effective_status' => json_encode(['ACTIVE']),
                'limit' => 200,
            ]);
            $sum = 0;
            foreach (($resp['data'] ?? []) as $a) {
                $sum += (int)($a['daily_budget'] ?? 0);
            }
            return $sum / 100;
        });
    }

    public function ads(string $adsetId): array
    {
        return Cache::remember("koshka:ads:{$adsetId}", 300, function () use ($adsetId) {
            $r = $this->get("/{$adsetId}/ads", [
                'fields' => 'id,name,status,effective_status,creative{id,name}',
                'limit' => 25,
            ]);
            return $r['data'] ?? [];
        });
    }

    public function previewHtml(string $adId, string $format = 'MOBILE_FEED_STANDARD'): string
    {
        $cacheKey = "koshka:preview:{$adId}:{$format}";
        return Cache::remember($cacheKey, 3600, function () use ($adId, $format) {
            $r = $this->get("/{$adId}/previews", ['ad_format' => $format]);
            return $r['data'][0]['body'] ?? '';
        });
    }

    public function adsets(string $campaignId): array
    {
        $resp = $this->get("/{$campaignId}/adsets", [
            'fields' => 'id,name,status,daily_budget,lifetime_budget,start_time,end_time,optimization_goal',
            'limit' => 50,
        ]);
        return $resp['data'] ?? [];
    }

    // --- WRITES ---

    public function setCampaignStatus(string $campaignId, string $status): array
    {
        $this->clearCache();
        return $this->post("/{$campaignId}", ['status' => strtoupper($status)]);
    }

    public function renameCampaign(string $campaignId, string $name): array
    {
        $this->clearCache();
        return $this->post("/{$campaignId}", ['name' => $name]);
    }

    public function duplicateCampaign(string $campaignId, ?string $newName = null): array
    {
        $this->clearCache();
        $data = ['deep_copy' => 'true', 'status_option' => 'PAUSED'];
        if ($newName) $data['rename_options'] = json_encode(['rename_suffix' => ' - העתק']);
        return $this->post("/{$campaignId}/copies", $data);
    }

    public function setAdsetStatus(string $adsetId, string $status): array
    {
        $this->clearCache();
        return $this->post("/{$adsetId}", ['status' => strtoupper($status)]);
    }

    public function setAdsetBudget(string $adsetId, float $amountIls, string $type = 'daily'): array
    {
        $this->clearCache();
        $cents = (int) round($amountIls * 100);
        return $this->post("/{$adsetId}", [
            $type === 'lifetime' ? 'lifetime_budget' : 'daily_budget' => $cents,
        ]);
    }

    /** Adjust all ad sets of a campaign by % (e.g. -20 = lower 20%, +30 = raise 30%). */
    public function adjustCampaignBudgetPct(string $campaignId, float $pct): array
    {
        $this->clearCache();
        $results = ['updated' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($this->adsets($campaignId) as $as) {
            $current = (int)($as['daily_budget'] ?? 0);
            if ($current <= 0) { $results['skipped']++; continue; }
            $new = max(100, (int) round($current * (1 + $pct / 100))); // min ₪1
            $r = $this->post("/{$as['id']}", ['daily_budget' => $new]);
            if (isset($r['error'])) $results['errors'][] = $r['error']['message'] ?? 'err';
            else $results['updated']++;
        }
        return $results;
    }

    /** Add X shekels to every daily budget. */
    public function addCampaignBudget(string $campaignId, float $ilsToAdd): array
    {
        $this->clearCache();
        $cents = (int) round($ilsToAdd * 100);
        $results = ['updated' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($this->adsets($campaignId) as $as) {
            $current = (int)($as['daily_budget'] ?? 0);
            if ($current <= 0) { $results['skipped']++; continue; }
            $new = $current + $cents;
            $r = $this->post("/{$as['id']}", ['daily_budget' => $new]);
            if (isset($r['error'])) $results['errors'][] = $r['error']['message'] ?? 'err';
            else $results['updated']++;
        }
        return $results;
    }

    public function setAdsetSchedule(string $adsetId, ?string $start, ?string $end): array
    {
        $this->clearCache();
        $data = [];
        if ($start) $data['start_time'] = $start;
        if ($end) $data['end_time'] = $end;
        return $this->post("/{$adsetId}", $data);
    }

    // --- LEADS ---

    /** Get Page Access Token (derived from system user token, used for /page/leadgen_forms). */
    public function pageAccessToken(string $pageId): ?string
    {
        return Cache::remember("koshka:pat:{$pageId}", 3600, function () use ($pageId) {
            $r = $this->get("/{$pageId}", ['fields' => 'access_token']);
            return $r['access_token'] ?? null;
        });
    }

    /** List all leadgen forms on a Page. Returns [{id,name,status,leads_count}]. */
    public function pageLeadgenForms(string $pageId): array
    {
        return Cache::remember("koshka:forms:{$pageId}", 300, function () use ($pageId) {
            $pat = $this->pageAccessToken($pageId);
            if (!$pat) return [];
            $resp = Http::timeout(30)->get(self::GRAPH . "/{$pageId}/leadgen_forms", [
                'fields' => 'id,name,status,leads_count',
                'limit' => 100,
                'access_token' => $pat,
            ]);
            if (!$resp->ok()) {
                Log::warning("Meta forms list error", ['body' => substr($resp->body(), 0, 300)]);
                return [];
            }
            return $resp->json()['data'] ?? [];
        });
    }

    /**
     * Fetch leads for a given form. Newest first.
     * Returns [{id, created_time, ad_id, ad_name, campaign_name, field_data:[{name,values}]}, ...]
     */
    public function formLeads(string $formId, int $limit = 100): array
    {
        $resp = Http::timeout(30)->get(self::GRAPH . "/{$formId}/leads", [
            'fields' => 'id,created_time,ad_id,ad_name,campaign_name,adset_name,field_data',
            'limit' => $limit,
            'access_token' => $this->token,
        ]);
        if (!$resp->ok()) {
            Log::warning("Meta leads fetch error", ['form' => $formId, 'body' => substr($resp->body(), 0, 300)]);
            return [];
        }
        return $resp->json()['data'] ?? [];
    }

    /** Aggregate all leads across a Page's active forms, newest first. */
    public function pageRecentLeads(string $pageId, int $perForm = 50): array
    {
        $forms = $this->pageLeadgenForms($pageId);
        $all = [];
        foreach ($forms as $f) {
            if (($f['status'] ?? '') !== 'ACTIVE') continue;
            $leads = $this->formLeads($f['id'], $perForm);
            foreach ($leads as $l) {
                $l['_form_name'] = $f['name'] ?? '';
                $l['_form_id'] = $f['id'];
                $all[] = $l;
            }
        }
        usort($all, fn($a, $b) => strcmp($b['created_time'] ?? '', $a['created_time'] ?? ''));
        return $all;
    }

    // --- HELPERS ---

    protected function extractLeads(array $actions): int
    {
        foreach ($actions as $a) {
            if (in_array($a['action_type'] ?? '', ['lead', 'offsite_conversion.fb_pixel_lead', 'onsite_conversion.lead_grouped'])) {
                return (int) $a['value'];
            }
        }
        return 0;
    }

    protected function pctDelta(float $prev, float $now): ?float
    {
        if ($prev == 0) return $now > 0 ? null : 0;
        return round((($now - $prev) / $prev) * 100, 1);
    }

    protected function get(string $path, array $params = []): array
    {
        $params['access_token'] = $this->token;
        $resp = Http::timeout(30)->get(self::GRAPH . $path, $params);
        if (!$resp->ok()) Log::warning("Meta GET error", ['path' => $path, 'body' => substr($resp->body(), 0, 300)]);
        return $resp->json() ?? [];
    }

    protected function post(string $path, array $data = []): array
    {
        $data['access_token'] = $this->token;
        $resp = Http::timeout(30)->asForm()->post(self::GRAPH . $path, $data);
        if (!$resp->ok()) Log::warning("Meta POST error", ['path' => $path, 'body' => substr($resp->body(), 0, 300)]);
        return $resp->json() ?? [];
    }

    public function clearCache(): void
    {
        Cache::forget("koshka:today:{$this->accountId}");
        Cache::forget("koshka:weekcompare:{$this->accountId}");
        Cache::forget("koshka:campaigns:{$this->accountId}");
        Cache::forget("koshka:month:{$this->accountId}");
        Cache::forget("koshka:activedaily:{$this->accountId}");
    }
}
