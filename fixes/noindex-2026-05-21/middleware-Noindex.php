<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Noindex
 *
 * Global hard-block on search engine indexing.
 *
 * - Sends `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet` on every response.
 *   This covers HTML, JSON, images, PDFs — anything Googlebot may fetch.
 * - For HTML responses, injects `<meta name="robots" content="noindex,nofollow">`
 *   into the <head> as a belt-and-suspenders for crawlers that ignore the header
 *   (or for cached HTML served by a CDN that strips headers).
 *
 * Intent: analytics.seokru.com is an internal/private app. It must never appear
 * in any search index. Place at the TOP of the global $middleware stack in
 * App\Http\Kernel so it applies to every route, including errors and assets.
 */
class Noindex
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // 1. Always set the header. Cheap, universal, covers non-HTML responses.
        $response->headers->set(
            'X-Robots-Tag',
            'noindex, nofollow, noarchive, nosnippet'
        );

        // 2. For HTML responses, inject a <meta robots> tag into <head>.
        //    Only attempt this on text/html responses with a body containing </head>.
        $contentType = (string) $response->headers->get('Content-Type', '');
        if (stripos($contentType, 'text/html') !== false) {
            $content = $response->getContent();

            if (is_string($content) && stripos($content, '</head>') !== false
                && stripos($content, 'name="robots"') === false) {
                $meta = '<meta name="robots" content="noindex,nofollow">' . "\n";
                $content = preg_replace(
                    '/<\/head>/i',
                    $meta . '</head>',
                    $content,
                    1
                );
                $response->setContent($content);
            }
        }

        return $response;
    }
}
