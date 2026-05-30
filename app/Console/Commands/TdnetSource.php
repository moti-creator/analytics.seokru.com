<?php

namespace App\Console\Commands;

use App\Models\TdnetLead;
use App\Services\ApifyService;
use Illuminate\Console\Command;

class TdnetSource extends Command
{
    protected $signature = 'tdnet:source
        {--count=10 : Number of leads to fetch}
        {--country=* : Country filter (default: TDNet non-US ICP set)}
        {--titles=* : Job title overrides}';

    protected $description = 'Source TDNet prospects via Apify x_guru/Leads-Scraper-apollo-zoominfo';

    /**
     * Country names accepted by x_guru actor (capitalized).
     */
    protected array $defaultCountries = [
        'United Kingdom', 'Germany', 'Netherlands', 'France', 'Sweden',
        'Switzerland', 'Australia', 'Canada', 'Israel', 'Singapore', 'Japan',
    ];

    public function handle(ApifyService $apify): int
    {
        if (!$apify->available()) {
            $this->error('APIFY_TOKEN not set');
            return self::FAILURE;
        }

        $count = max(1, (int) $this->option('count'));
        $countriesIn = $this->option('country') ?: [];
        $countries = $countriesIn ? array_map([$this, 'normalizeCountry'], $countriesIn) : $this->defaultCountries;

        $titles = $this->option('titles') ?: [
            'medical librarian', 'clinical librarian', 'health sciences librarian',
            'informationist', 'information specialist', 'medical information specialist',
            'library manager', 'knowledge manager', 'corporate librarian',
            'electronic resources librarian',
        ];

        // Over-fetch — actor's email_status filter is unreliable, ~30-50% lack email
        $fetchCount = min(50, $count * 4);
        $this->info("Sourcing {$count} leads (fetching {$fetchCount} to filter for email) from " . implode(', ', $countries));

        $items = $apify->runSync('x_guru/Leads-Scraper-apollo-zoominfo', [
            'max_results' => $fetchCount,
            'job_titles' => $titles,
            'person_location_country' => $countries,
            'email_status' => 'verified',
            'include_emails' => true,
        ]);

        if (empty($items)) {
            $this->warn('No leads returned from Apify.');
            return self::SUCCESS;
        }

        $inserted = 0;
        $skippedNoEmail = 0;
        $skippedDupe = 0;
        $skippedFreemail = 0;
        $freemailDomains = ['gmail.com','yahoo.com','hotmail.com','outlook.com','icloud.com','protonmail.com','aol.com','live.com','me.com','googlemail.com'];

        foreach ($items as $item) {
            if ($inserted >= $count) break;

            // Prefer work_email; fall back to personal_emails[0]
            $emailSource = 'work_email';
            $email = $item['work_email'] ?? null;
            if (!$email && !empty($item['personal_emails'][0])) {
                $email = $item['personal_emails'][0];
                $emailSource = 'personal_emails';
            }
            if (!$email) { $skippedNoEmail++; continue; }

            $domain = strtolower(substr($email, strrpos($email, '@') + 1));
            if (in_array($domain, $freemailDomains)) { $skippedFreemail++; continue; }

            if (TdnetLead::where('email', $email)->exists()) { $skippedDupe++; continue; }

            [$first, $last] = $this->splitName($item['full_name'] ?? '');
            $segment = $this->classifySegment($item);
            $company = $this->titleCase($item['job_company_name'] ?? null);
            [$emailQuality, $emailQualityReason] = $this->assessEmail($email, $emailSource, $company);

            TdnetLead::create([
                'first_name'           => $first,
                'last_name'            => $last,
                'position'             => $this->titleCase($item['job_title'] ?? null),
                'company'              => $company,
                'country'              => $this->titleCase($item['location_country'] ?? null),
                'email'                => $email,
                'email_quality'        => $emailQuality,
                'email_quality_reason' => $emailQualityReason,
                'linkedin_url'         => $item['linkedin_url'] ?? null,
                'segment'              => $segment,
                'source_meta'          => $item,
                'status'               => 'new',
            ]);
            $inserted++;
        }

        $this->info("Inserted {$inserted}. Skipped — no email: {$skippedNoEmail}, freemail: {$skippedFreemail}, dupes: {$skippedDupe}.");
        return self::SUCCESS;
    }

    protected function normalizeCountry(string $c): string
    {
        $map = [
            'united kingdom' => 'United Kingdom', 'uk' => 'United Kingdom',
            'germany' => 'Germany', 'netherlands' => 'Netherlands',
            'france' => 'France', 'sweden' => 'Sweden',
            'switzerland' => 'Switzerland', 'australia' => 'Australia',
            'canada' => 'Canada', 'israel' => 'Israel',
            'singapore' => 'Singapore', 'japan' => 'Japan',
            'india' => 'India', 'china' => 'China', 'spain' => 'Spain',
            'italy' => 'Italy', 'south korea' => 'South Korea',
            'united arab emirates' => 'United Arab Emirates',
        ];
        $key = strtolower(trim($c));
        return $map[$key] ?? ucwords($key);
    }

    protected function splitName(string $full): array
    {
        $full = trim($full);
        if ($full === '') return [null, null];
        $parts = preg_split('/\s+/', $this->titleCase($full));
        $first = array_shift($parts);
        $last = $parts ? implode(' ', $parts) : null;
        return [$first, $last];
    }

    protected function titleCase(?string $s): ?string
    {
        if (!$s) return $s;
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Email-quality heuristic.
     * @return array{0:string,1:?string}  [quality, reason]
     */
    protected function assessEmail(string $email, string $source, ?string $company): array
    {
        $domain = strtolower(substr($email, strrpos($email, '@') + 1));
        if ($source === 'personal_emails') {
            return ['unknown', 'fell back to personal_emails — may be old/personal address'];
        }
        if (!$company) {
            return ['unknown', 'no company recorded to cross-check'];
        }
        // Normalize company name → token set
        $compTokens = array_filter(preg_split('/[^a-z0-9]+/', strtolower($company)));
        $compTokens = array_diff($compTokens, ['inc','ltd','llc','plc','gmbh','sa','ag','bv','nv','co','corp','group','holdings','limited','company','the','of','for']);
        $domainStem = preg_replace('/\.(com|org|net|co\.uk|ac\.uk|nhs\.uk|edu|gov|io|de|fr|nl|au|ca|jp|sg|se|ch)$/i', '', $domain);
        $domainStem = strtolower(str_replace(['-','.'], '', $domainStem));
        foreach ($compTokens as $tok) {
            if (strlen($tok) >= 3 && str_contains($domainStem, $tok)) {
                return ['ok', null];
            }
        }
        return ['stale', "email domain `{$domain}` doesn't match company `{$company}` — prospect may have moved"];
    }

    protected function classifySegment(array $item): string
    {
        $industry = strtolower($item['industry'] ?? '');
        $company = strtolower($item['job_company_name'] ?? '');
        $title = strtolower($item['job_title'] ?? '');

        if (str_contains($industry, 'pharma') || str_contains($industry, 'biotech') ||
            str_contains($industry, 'medical device') || str_contains($company, 'pfizer') ||
            str_contains($company, 'novartis') || str_contains($company, 'roche') ||
            str_contains($company, 'sanofi') || str_contains($company, 'gsk') ||
            str_contains($company, 'astrazeneca') || str_contains($company, 'bayer') ||
            str_contains($company, 'merck') || str_contains($company, 'takeda')) {
            return 'pharma';
        }
        if (str_contains($industry, 'higher education') || str_contains($industry, 'research') ||
            str_contains($company, 'universit') || str_contains($company, 'college') ||
            str_contains($company, 'institute')) {
            return 'academic';
        }
        if (str_contains($industry, 'hospital') || str_contains($industry, 'libraries') ||
            str_contains($company, 'hospital') || str_contains($company, 'nhs') ||
            str_contains($company, 'health') || str_contains($title, 'clinical')) {
            return 'hospital';
        }
        return 'corporate';
    }
}
