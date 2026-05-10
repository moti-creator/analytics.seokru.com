<?php

namespace App\Support;

use DOMDocument;
use DOMNode;
use DOMElement;
use DOMXPath;

/**
 * Allowlist-based HTML sanitizer for LLM-produced narrative.
 * Strips scripts, event handlers, javascript: URLs, and any tags/attributes
 * not explicitly allowed. Preserves report formatting and QuickChart images.
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'table', 'thead', 'tbody', 'tr', 'td', 'th',
        'a', 'img',
        'div', 'span', 'blockquote', 'code', 'pre',
    ];

    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        '*' => ['class'],
    ];

    private const URL_ATTRS = ['href', 'src'];

    public static function clean(string $html): string
    {
        if (trim($html) === '') return '';

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"?><div id="__sanroot">' . $html . '</div>';
        $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $root = $xpath->query('//*[@id="__sanroot"]')->item(0);
        if (!$root) return strip_tags($html);

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }
        return $out;
    }

    private static function walk(DOMNode $node): void
    {
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            if (!$child instanceof DOMElement) continue;

            $tag = strtolower($child->nodeName);

            if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            $allowed = array_merge(self::ALLOWED_ATTRS['*'] ?? [], self::ALLOWED_ATTRS[$tag] ?? []);
            $attrs = [];
            foreach ($child->attributes as $a) $attrs[] = $a->nodeName;
            foreach ($attrs as $name) {
                $lname = strtolower($name);
                if (!in_array($lname, $allowed, true) || str_starts_with($lname, 'on')) {
                    $child->removeAttribute($name);
                    continue;
                }
                if (in_array($lname, self::URL_ATTRS, true)) {
                    $val = trim($child->getAttribute($name));
                    if (!self::safeUrl($val)) {
                        $child->removeAttribute($name);
                        continue;
                    }
                }
            }

            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener noreferrer');
            }

            self::walk($child);
        }
    }

    private static function safeUrl(string $url): bool
    {
        if ($url === '') return false;
        if (str_starts_with($url, '/') || str_starts_with($url, '#') || str_starts_with($url, '?')) return true;
        if (preg_match('#^https?://#i', $url)) return true;
        if (preg_match('#^mailto:#i', $url)) return true;
        return false;
    }
}
