<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Apify API client — sync run-and-get-dataset.
 */
class ApifyService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.apify.token') ?? '';
    }

    public function available(): bool
    {
        return $this->token !== '';
    }

    /**
     * Run actor synchronously and return dataset items.
     * Actor must be quick (<5min). For TDNet we use code_crafter/leads-finder.
     */
    public function runSync(string $actorId, array $input, int $timeoutSec = 300): array
    {
        $url = "https://api.apify.com/v2/acts/{$actorId}/run-sync-get-dataset-items";

        $resp = Http::timeout($timeoutSec)
            ->withHeaders(['Authorization' => "Bearer {$this->token}"])
            ->post($url, $input);

        if (!$resp->ok()) {
            Log::warning('Apify run failed', ['actor' => $actorId, 'status' => $resp->status(), 'body' => substr($resp->body(), 0, 500)]);
            return [];
        }

        $data = $resp->json();
        return is_array($data) ? $data : [];
    }
}
