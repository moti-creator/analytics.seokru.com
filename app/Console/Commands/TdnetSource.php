<?php

namespace App\Console\Commands;

use App\Models\TdnetLead;
use App\Services\ApifyService;
use Illuminate\Console\Command;

class TdnetSource extends Command
{
    protected $signature = 'tdnet:source
        {--count=20 : Number of leads to fetch}
        {--country=* : Country filter (default: non-US ICP set)}
        {--titles=* : Job title overrides}';

    protected $description = 'Source TDNet prospects via Apify Leads Finder';

    public function handle(ApifyService $apify): int
    {
        if (!$apify->available()) {
            $this->error('APIFY_TOKEN not set');
            return self::FAILURE;
        }

        $count = (int) $this->option('count');
        $countries = $this->option('country') ?: [
            'united kingdom', 'germany', 'netherlands', 'france', 'sweden',
            'switzerland', 'australia', 'canada', 'israel', 'singapore', 'japan',
        ];
        $titles = $this->option('titles') ?: [
            'medical librarian', 'clinical librarian', 'health sciences librarian',
            'informationist', 'information specialist', 'medical information specialist',
            'library manager', 'knowledge manager', 'corporate librarian',
            'r&d information manager', 'electronic resources librarian',
        ];

        $this->info("Sourcing {$count} leads from " . implode(', ', $countries));

        $items = $apify->runSync('code_crafter/leads-finder', [
            'fetch_count' => $count,
            'file_name' => 'TDNet_Prospects_' . now()->format('Y-m-d'),
            'contact_job_title' => $titles,
            'contact_not_location' => ['united states'],
            'contact_location' => $countries,
            'company_industry' => [
                'hospital & health care', 'pharmaceuticals',
                'higher education', 'biotechnology', 'medical practice',
                'research', 'medical devices',
            ],
            'email_status' => ['validated'],
        ]);

        if (empty($items)) {
            $this->warn('No leads returned from Apify.');
            return self::SUCCESS;
        }

        $inserted = 0;
        foreach ($items as $item) {
            $email = $item['email'] ?? null;
            if (!$email) continue;

            // Skip dupes
            if (TdnetLead::where('email', $email)->exists()) continue;

            $segment = $this->classifySegment($item);

            TdnetLead::create([
                'first_name'   => $item['first_name'] ?? $item['firstName'] ?? null,
                'last_name'    => $item['last_name'] ?? $item['lastName'] ?? null,
                'position'     => $item['title'] ?? $item['job_title'] ?? null,
                'company'      => $item['company_name'] ?? $item['organization_name'] ?? null,
                'country'      => $item['country'] ?? null,
                'email'        => $email,
                'linkedin_url' => $item['linkedin_url'] ?? null,
                'segment'      => $segment,
                'source_meta'  => $item,
                'status'       => 'new',
            ]);
            $inserted++;
        }

        $this->info("Inserted {$inserted} new leads.");
        return self::SUCCESS;
    }

    protected function classifySegment(array $item): string
    {
        $industry = strtolower($item['company_industry'] ?? '');
        $company = strtolower($item['company_name'] ?? $item['organization_name'] ?? '');
        $title = strtolower($item['title'] ?? $item['job_title'] ?? '');

        if (str_contains($industry, 'pharma') || str_contains($industry, 'biotech') || str_contains($industry, 'medical device')) {
            return 'pharma';
        }
        if (str_contains($industry, 'higher education') || str_contains($company, 'universit') || str_contains($company, 'college')) {
            return 'academic';
        }
        if (str_contains($industry, 'hospital') || str_contains($company, 'hospital') || str_contains($company, 'health') || str_contains($title, 'clinical')) {
            return 'hospital';
        }
        return 'corporate';
    }
}
