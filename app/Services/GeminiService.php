<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Single-shot LLM caller for ReportBuilder narratives.
 *
 * Backend choice differs from AgentService by design:
 *   - Reports (here): Groq first (cheap, fast, no tool calls needed).
 *   - Agent / ask (AgentService::pickBackend): Gemini first because tool-calling
 *     quality and JSON schema adherence are stronger; falls over to Groq on 429.
 * Keep both in sync only if you change the strategy globally.
 */
class GeminiService
{
    public function raw(string $prompt): string
    {
        // Prefer Groq (faster, higher free tier). Fall back to Gemini if no key.
        $groq = new GroqService();
        if ($groq->available()) {
            return $groq->raw($prompt);
        }

        $model = config('services.gemini.model');
        $key = config('services.gemini.key');

        $resp = Http::timeout(60)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}",
            ['contents' => [['parts' => [['text' => $prompt]]]]]
        );

        $json = $resp->json();
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? '<p>Report generation failed.</p>';
    }

    public function narrate(array $metrics): string
    {
        return $this->raw("Write a brief weekly HTML report from: " . json_encode($metrics));
    }
}
