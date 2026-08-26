<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

class RichTextSanitizer
{
    private const ALLOWED_TAGS = [
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'p', 'ul', 'ol', 'li', 'strong', 'em',
        'a', 'img', 'figure', 'figcaption', 'blockquote', 'code', 'pre',
        'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'br', 'hr', 'span', 'div',
    ];

    private const ALLOWED_ATTRS = [
        'a' => ['href', 'title'],
        'img' => ['src', 'alt', 'title'],
        'code' => ['class'],
        'pre' => ['class'],
        'span' => ['class'],
        'div' => ['class'],
        'table' => ['class'],
        'th' => ['scope', 'class', 'style'],
        'td' => ['class', 'style'],
    ];

    private const DROP_TAGS = [
        'script', 'style', 'iframe', 'frame', 'frameset', 'object', 'embed',
        'portal', 'template', 'noscript', 'svg', 'math', 'form', 'input',
        'button', 'textarea', 'select', 'option', 'video', 'audio', 'source',
        'canvas', 'link', 'meta', 'base', 'applet',
    ];

    public static function render(string $markdown): string
    {
        return static::decorateImages(static::sanitize(static::markdownToHtml($markdown)));
    }

    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');

        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="rich-content">'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NONET | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $body = $dom->getElementById('rich-content');

        if (! $body) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        static::walk($dom, $body);

        $inner = '';
        foreach ($body->childNodes as $child) {
            $inner .= $dom->saveHTML($child);
        }

        return trim($inner);
    }

    /**
     * Force performance-friendly attributes on every rendered image.
     */
    private static function decorateImages(string $html): string
    {
        return (string) preg_replace('/<img\b(?![^>]*\bloading=)/i', '<img loading="lazy" decoding="async"', $html);
    }

    public static function sanitizeSvg(?string $svg): string
    {
        if ($svg === null || trim($svg) === '') {
            return '';
        }

        $svg = preg_replace('/<!--.*?-->/s', '', $svg);
        $svg = preg_replace('/<!\[CDATA\[.*?\]\]>/s', '', $svg);
        $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
        $svg = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $svg);
        $svg = preg_replace('#<foreignObject\b.*?</foreignObject>#is', '', $svg);
        $svg = preg_replace('#<use\b[^>]*/>#i', '', $svg);
        $svg = preg_replace('/\son[a-z]+\s*=\s*(["\']).*?\1/i', '', $svg);
        $svg = preg_replace('/\shref\s*=\s*(["\'])javascript:.*?\1/i', '', $svg);
        $svg = preg_replace('/\s+xlink:href\s*=\s*(["\'])javascript:.*?\1/i', '', $svg);
        $svg = preg_replace('/\sstyle\s*=\s*(["\']).*?\1/i', '', $svg);

        return trim((string) $svg);
    }

    private static function walk(DOMDocument $dom, DOMElement $node): void
    {
        $tag = strtolower($node->tagName);

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            if (in_array($tag, self::DROP_TAGS, true)) {
                $node->parentNode->removeChild($node);
            } else {
                static::unwrap($dom, $node);
            }

            return;
        }

        static::cleanAttributes($node);

        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                static::walk($dom, $child);
            }
        }
    }

    private static function unwrap(DOMDocument $dom, DOMElement $node): void
    {
        $children = iterator_to_array($node->childNodes);

        while ($node->firstChild) {
            $node->parentNode->insertBefore($node->firstChild, $node);
        }
        $node->parentNode->removeChild($node);

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                static::walk($dom, $child);
            }
        }
    }

    private static function cleanAttributes(DOMElement $node): void
    {
        $tag = strtolower($node->tagName);
        $allowed = self::ALLOWED_ATTRS[$tag] ?? [];

        foreach (iterator_to_array($node->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on')) {
                $node->removeAttribute($attribute->name);

                continue;
            }

            if ($name === 'href' && $tag === 'a') {
                if (! static::isSafeUrl($attribute->value)) {
                    $node->removeAttribute($attribute->name);
                } else {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }

                continue;
            }

            if ($tag === 'img') {
                if ($name === 'src' && ! static::isSafeUrl($attribute->value)) {
                    $node->removeAttribute($attribute->name);

                    continue;
                }

                if (! in_array($name, ['src', 'alt', 'title'], true)) {
                    $node->removeAttribute($attribute->name);
                }

                continue;
            }

            if (! in_array($name, $allowed, true)) {
                $node->removeAttribute($attribute->name);
            }
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);

        if ($url === '') {
            return false;
        }

        $compact = preg_replace('/[\x00-\x20\x7f]+/', '', $url);
        $lower = strtolower((string) $compact);

        if (
            str_starts_with($lower, 'javascript:')
            || str_starts_with($lower, 'vbscript:')
            || str_starts_with($lower, 'data:')
            || str_starts_with($lower, 'file:')
        ) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url)) {
            return str_starts_with($lower, 'http://')
                || str_starts_with($lower, 'https://')
                || str_starts_with($lower, 'mailto:')
                || str_starts_with($lower, 'tel:');
        }

        return true;
    }

    /**
     * Extract the first image (or figure containing an image) from HTML.
     * Returns [imageHtml, src] or [null, null] if no image found.
     */
    public static function extractFirstImage(string $html): array
    {
        if (preg_match('/<figure[^>]*>\s*<img\b[^>]*>.*?<\/figure>/is', $html, $m)) {
            preg_match('/<img[^>]*\bsrc=["\']([^"\']+)["\']/i', $m[0], $src);

            return [$m[0], $src[1] ?? null];
        }

        if (preg_match('/<img\b[^>]*>/i', $html, $m)) {
            preg_match('/\bsrc=["\']([^"\']+)["\']/i', $m[0], $src);

            return [$m[0], $src[1] ?? null];
        }

        return [null, null];
    }

    /**
     * Remove the first image (or figure containing an image) from HTML.
     */
    public static function stripFirstImage(string $html): string
    {
        $html = preg_replace('/<figure[^>]*>\s*<img\b[^>]*>.*?<\/figure>/is', '', $html, 1);

        return preg_replace('/<img\b[^>]*>/i', '', $html, 1);
    }

    private static function markdownToHtml(string $markdown): string
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);
        $lines = preg_split('/\n/', $markdown);
        $html = [];
        $inCodeBlock = false;
        $codeLang = '';
        $codeLines = [];
        $paragraph = [];
        $listType = null;

        $flushParagraph = function () use (&$paragraph, &$html) {
            if ($paragraph !== []) {
                $html[] = '<p>'.implode('<br>', array_map(
                    fn ($line) => static::inline(trim($line)),
                    $paragraph
                )).'</p>';
                $paragraph = [];
            }
        };

        $flushCode = function () use (&$codeLines, &$codeLang, &$html) {
            if ($codeLines !== []) {
                $langClass = $codeLang !== '' ? ' class="language-'.htmlspecialchars($codeLang, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"' : '';
                $html[] = '<pre><code'.$langClass.'>'.htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'</code></pre>';
                $codeLines = [];
                $codeLang = '';
            }
        };

        $flushList = function (&$listType) use (&$html) {
            if ($listType !== null) {
                $html[] = '</'.$listType.'>';
                $listType = null;
            }
        };

        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if (str_starts_with($trimmed, '```')) {
                $flushParagraph();
                if ($inCodeBlock) {
                    $flushCode();
                    $inCodeBlock = false;
                } else {
                    $inCodeBlock = true;
                    $codeLang = trim(substr($trimmed, 3));
                }

                continue;
            }

            if ($inCodeBlock) {
                if (preg_match('/^!\[[^\]]*\]\([^)]+\)/', $trimmed) || preg_match('/^#{1,6}\s+\S/', $trimmed)) {
                    // Auto-close an unclosed fence so trailing images/headings aren't swallowed
                    // by an author who forgot the closing code fence.
                    $flushCode();
                    $inCodeBlock = false;
                } else {
                    $codeLines[] = $line;

                    continue;
                }
            }

            if ($trimmed === '') {
                $flushParagraph();
                $flushList($listType);

                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushList($listType);
                $level = strlen($m[1]);
                $html[] = '<h'.$level.'>'.static::inline($m[2]).'</h'.$level.'>';

                continue;
            }

            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                $flushParagraph();
                $flushList($listType);
                $html[] = '<hr>';

                continue;
            }

            if (preg_match('/^!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)$/', $trimmed, $m)) {
                $flushParagraph();
                $flushList($listType);
                $html[] = '<figure><img src="'.htmlspecialchars($m[2], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                    .'" alt="'.htmlspecialchars($m[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'"'
                    .(! isset($m[3]) || trim($m[3]) === ''
                        ? '>'
                        : '><figcaption>'.static::inline(trim($m[3])).'</figcaption>')
                    .'</figure>';

                continue;
            }

            if (str_starts_with($trimmed, '>')) {
                $flushParagraph();
                $flushList($listType);
                $quote = trim(substr($trimmed, 1));
                $html[] = '<blockquote><p>'.static::inline($quote).'</p></blockquote>';

                continue;
            }

            if (preg_match('/^[-*+]\s+(.+)$/', $trimmed, $m)) {
                $flushParagraph();
                if ($listType !== 'ul') {
                    $flushList($listType);
                    $html[] = '<ul>';
                    $listType = 'ul';
                }
                $html[] = '<li>'.static::inline($m[1]).'</li>';

                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $m)) {
                $flushParagraph();
                if ($listType !== 'ol') {
                    $flushList($listType);
                    $html[] = '<ol>';
                    $listType = 'ol';
                }
                $html[] = '<li>'.static::inline($m[1]).'</li>';

                continue;
            }

            if (preg_match('/^\|(.+)\|?\s*$/', $trimmed)) {
                $flushParagraph();
                $flushList($listType);

                $tableLines = [];
                while ($i < $count && preg_match('/^\|(.+)\|?\s*$/', trim($lines[$i]))) {
                    $tableLines[] = trim($lines[$i]);
                    $i++;
                }
                $i--;

                $sepIdx = -1;
                for ($j = 1, $tCount = count($tableLines); $j < $tCount; $j++) {
                    if (preg_match('/^[\s|:\-]+$/', $tableLines[$j]) && strpos($tableLines[$j], '-') !== false) {
                        $sepIdx = $j;
                        break;
                    }
                }

                if ($sepIdx > 0 && count($tableLines) >= 3) {
                    $parseCells = static function (string $row): array {
                        $row = trim($row, " \t|");

                        return array_map(fn ($c) => static::inline(trim($c)), explode('|', $row));
                    };

                    $aligns = array_map(function (string $cell): ?string {
                        $cell = trim($cell);
                        if (str_starts_with($cell, ':') && str_ends_with($cell, ':')) {
                            return 'center';
                        }
                        if (str_ends_with($cell, ':')) {
                            return 'right';
                        }

                        return null;
                    }, explode('|', trim($tableLines[$sepIdx], " \t|")));

                    $html[] = '<div class="overflow-x-auto">';
                    $html[] = '<table>';
                    $html[] = '<thead><tr>';
                    foreach ($parseCells($tableLines[$sepIdx - 1]) as $idx => $th) {
                        $style = isset($aligns[$idx]) && $aligns[$idx] !== null
                            ? ' style="text-align:'.$aligns[$idx].'"'
                            : '';
                        $html[] = '<th'.$style.'>'.$th.'</th>';
                    }
                    $html[] = '</tr></thead>';
                    $html[] = '<tbody>';
                    for ($j = $sepIdx + 1, $tCount = count($tableLines); $j < $tCount; $j++) {
                        $html[] = '<tr>';
                        foreach ($parseCells($tableLines[$j]) as $idx => $td) {
                            $style = isset($aligns[$idx]) && $aligns[$idx] !== null
                                ? ' style="text-align:'.$aligns[$idx].'"'
                                : '';
                            $html[] = '<td'.$style.'>'.$td.'</td>';
                        }
                        $html[] = '</tr>';
                    }
                    $html[] = '</tbody>';
                    $html[] = '</table>';
                    $html[] = '</div>';
                } else {
                    foreach ($tableLines as $tl) {
                        $html[] = '<p>'.static::inline(trim($tl, " \t|")).'</p>';
                    }
                }

                continue;
            }

            $paragraph[] = $line;
        }

        $flushParagraph();
        $flushList($listType);
        $flushCode();

        return implode("\n", $html);
    }

    private static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"[^"]*")?\)/', '<img src="$2" alt="$1">', $text);
        $text = preg_replace('/\[([^\]]+)\]\(([^)\s]+)(?:\s+"[^"]*")?\)/', '<a href="$2">$1</a>', $text);
        $text = preg_replace('/\*\*(?!\s)(.+?)(?<!\s)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(^|[^*])\*([^*\s][^*]*?)\*(?!\*)/', '$1<em>$2</em>', $text);

        return (string) $text;
    }
}
