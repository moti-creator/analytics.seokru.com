<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function narrate(array $metrics): string
    {
        $prompt = "You are a senior SEO/analytics consultant writing a weekly report for a small business owner in plain English.\n\n"
            . "Data (GA4 + Google Search Console, this week vs prior week):\n"
            . json_encode($metrics, JSON_PRETTY_PRINT) . "\n\n"
            . "Write a report with these exact HTML sections (use <h2> and <p>/<ul>):\n"
            . "1. Executive Summary (2-3 sentences)\n"
            . "2. What Grew (top wins with numbers)\n"
            . "3. What Dropped (concerns with numbers)\n"
            . "4. Why (likely reasons — hypothesis, label as hypothesis)\n"
            . "5. 3 Actions This Week (specific, prioritized)\n\n"
            . "Rules: plain English, no jargon, no markdown, only HTML tags listed. Be concise.";

        $model = config('services.gemini.model');
        $key = config('services.gemini.key');

        $resp = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            ['contents' => [['parts' => [['text' => $prompt]]]]]
        )->json();

        return $resp['candidates'][0]['content']['parts'][0]['text'] ?? '<p>Report generation failed.</p>';
    }
}
