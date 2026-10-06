<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allowlist HTML sanitizer for admin-authored rich text (blog bodies) that is output unescaped.
 *
 * Keeps basic formatting tags; drops every attribute except a safe http(s)/relative `href` on links and `src`/`alt`
 * on images; removes script/style/iframe/object/form elements together with their content; unwraps any other tag
 * (its text is kept). Event handlers, inline styles and javascript:/data: URLs can therefore never reach the page.
 */
class SafeHtml
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote',
        'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'img', 'hr', 'span', 'div', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'code', 'pre',
    ];

    /** Removed together with everything inside them. */
    private const DROP_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'template', 'noscript', 'link', 'meta', 'base'];

    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"?><div id="safe-html-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('safe-html-root');

        if (! $root) {
            return e(strip_tags($html));
        }

        self::cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $document->saveHTML($child);
        }

        return $output;
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        // Copy first: the live list changes while nodes are removed or unwrapped.
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node->nodeType === XML_COMMENT_NODE || $node->nodeType === XML_PI_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
                $parent->removeChild($node);

                continue;
            }

            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $parent->removeChild($node);

                continue;
            }

            self::cleanChildren($node);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                // Unknown tag: keep its (already cleaned) children, lose the tag itself.
                while ($node->firstChild) {
                    $parent->insertBefore($node->firstChild, $node);
                }
                $parent->removeChild($node);

                continue;
            }

            self::cleanAttributes($node, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $keep = match ($tag) {
            'a' => ['href'],
            'img' => ['src', 'alt'],
            default => [],
        };

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $keep, true) || (in_array($name, ['href', 'src'], true) && ! self::isSafeUrl($attribute->nodeValue))) {
                $element->removeAttribute($attribute->nodeName);
            }
        }

        if ($tag === 'a' && $element->hasAttribute('href')) {
            $element->setAttribute('rel', 'noopener noreferrer nofollow');
        }
    }

    private static function isSafeUrl(?string $url): bool
    {
        // Strip whitespace/control characters browsers ignore inside a scheme ("java\tscript:").
        $url = preg_replace('/[\x00-\x20]+/', '', (string) $url);

        if ($url === '') {
            return false;
        }

        if (preg_match('~^https?://~i', $url)) {
            return true;
        }

        // Relative URLs (/path, #anchor) only — anything with its own scheme is refused.
        return (bool) preg_match('~^(/(?!/)|#)~', $url) && ! preg_match('~^[a-z][a-z0-9+.\-]*:~i', $url);
    }
}
