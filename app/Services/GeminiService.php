<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function raw(string $prompt): string
    {
        $model = config('services.gemini.model');
        $key = config('services.gemini.key');

        $resp = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            ['contents' => [['parts' => [['text' => $prompt]]]]]
        );

        // Rate limit hit → fall back to Groq
        if ($resp->status() === 429) {
            Log::info('Gemini 429 → falling back to Groq');
            return $this->groqFallback($prompt);
        }

        $json = $resp->json();
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? $this->groqFallback($prompt);
    }

    protected function groqFallback(string $prompt): string
    {
        $groq = new GroqService();
        if (!$groq->available()) {
            return '<p>Report generation failed (rate limit hit, no fallback configured).</p>';
        }
        return $groq->raw($prompt);
    }

    public function narrate(array $metrics): string
    {
        return $this->raw("Write a brief weekly HTML report from: " . json_encode($metrics));
    }
}
