<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Generate /llms.txt — proposed standard for guiding LLM crawlers
 * to a curated content map. https://llmstxt.org
 *
 * Anthropic, Cloudflare and others publish one. Adoption is early
 * but cost is near-zero: produces a clean Markdown index of the site.
 */
class LlmsTxtService
{
    /**
     * Build llms.txt content from a sitemap URL.
     * Falls back to homepage scrape if sitemap unreachable.
     */
    public function build(string $domain, ?string $sitemapUrl = null): array
    {
        $domain = $this->normalizeDomain($domain);
        $sitemapUrl = $sitemapUrl ?: "https://{$domain}/sitemap.xml";

        $title = $this->fetchTitle("https://{$domain}");
        $description = $this->fetchMetaDescription("https://{$domain}");

        $urls = $this->fetchSitemapUrls($sitemapUrl);
        $sections = $this->groupByPath($urls);

        $md = "# {$title}\n\n";
        if ($description) {
            $md .= "> {$description}\n\n";
        }
        $md .= "Site map for LLM crawlers. Generated " . now()->toDateString() . " by SEOKRU Analytics.\n\n";

        foreach ($sections as $section => $links) {
            $md .= "## " . ucfirst($section) . "\n\n";
            foreach (array_slice($links, 0, 50) as $link) {
                $name = $this->urlToName($link);
                $md .= "- [{$name}]({$link})\n";
            }
            $md .= "\n";
        }

        return [
            'domain' => $domain,
            'sitemap_used' => $sitemapUrl,
            'url_count' => count($urls),
            'section_count' => count($sections),
            'content' => $md,
            'install_path' => "https://{$domain}/llms.txt",
        ];
    }

    protected function normalizeDomain(string $d): string
    {
        $d = preg_replace('#^https?://#', '', trim($d));
        return rtrim(strtok($d, '/'), '/');
    }

    protected function fetchTitle(string $url): string
    {
        try {
            $html = Http::timeout(8)->get($url)->body();
            if (preg_match('/<title>(.*?)<\/title>/is', $html, $m)) {
                return trim(strip_tags($m[1]));
            }
        } catch (\Throwable $e) {}
        return parse_url($url, PHP_URL_HOST) ?: 'Site';
    }

    protected function fetchMetaDescription(string $url): string
    {
        try {
            $html = Http::timeout(8)->get($url)->body();
            if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/is', $html, $m)) {
                return trim($m[1]);
            }
        } catch (\Throwable $e) {}
        return '';
    }

    protected function fetchSitemapUrls(string $sitemapUrl, int $depth = 0): array
    {
        if ($depth > 2) return [];
        try {
            $xml = Http::timeout(10)->get($sitemapUrl)->body();
            if (!$xml) return [];

            // Sitemap index? Recurse.
            if (str_contains($xml, '<sitemapindex')) {
                preg_match_all('#<loc>(.*?)</loc>#is', $xml, $m);
                $all = [];
                foreach (array_slice($m[1] ?? [], 0, 10) as $sub) {
                    $all = array_merge($all, $this->fetchSitemapUrls(trim($sub), $depth + 1));
                }
                return $all;
            }

            preg_match_all('#<loc>(.*?)</loc>#is', $xml, $m);
            return array_map('trim', $m[1] ?? []);
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function groupByPath(array $urls): array
    {
        $groups = [];
        foreach ($urls as $url) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $segs = array_values(array_filter(explode('/', $path)));
            $section = $segs[0] ?? 'home';
            $groups[$section][] = $url;
        }
        ksort($groups);
        return $groups;
    }

    protected function urlToName(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $slug = trim(basename($path), '/');
        if (!$slug) return parse_url($url, PHP_URL_HOST) ?: $url;
        return ucwords(str_replace(['-', '_'], ' ', $slug));
    }
}
