<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * GitHub Gist auto-creator. Public gists are crawled by Google
 * within hours — sometimes minutes. Each gist becomes a real backlink.
 *
 * Auth: GitHub Personal Access Token (PAT) with `gist` scope.
 * Set env: GITHUB_GIST_TOKEN
 */
class GistService
{
    public function publish(string $url, ?string $title = null, ?string $description = null): array
    {
        $token = env('GITHUB_GIST_TOKEN');
        if (!$token) {
            return ['ok' => false, 'reason' => 'GITHUB_GIST_TOKEN not configured'];
        }

        $title = $title ?: parse_url($url, PHP_URL_HOST);
        $description = $description ?: "Bookmark: {$url}";
        $domain = parse_url($url, PHP_URL_HOST);

        // Markdown content with the URL prominently linked
        $content = "# {$title}\n\n"
            . "**URL:** [{$url}]({$url})\n\n"
            . "**Domain:** {$domain}\n\n"
            . "{$description}\n\n"
            . "---\n*Generated " . now()->toDateString() . " by SEOKRU Analytics.*\n";

        $filename = preg_replace('/[^a-z0-9]+/i', '-', $domain) . '-' . now()->timestamp . '.md';

        try {
            $r = Http::timeout(15)
                ->withToken($token)
                ->withHeaders([
                    'Accept' => 'application/vnd.github+json',
                    'X-GitHub-Api-Version' => '2022-11-28',
                ])
                ->post('https://api.github.com/gists', [
                    'description' => "Bookmark: {$title} ({$domain})",
                    'public' => true,
                    'files' => [
                        $filename => ['content' => $content],
                    ],
                ]);

            return [
                'ok' => $r->successful(),
                'status' => $r->status(),
                'gist_url' => $r->json('html_url'),
                'gist_id' => $r->json('id'),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
