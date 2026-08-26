<?php

namespace Tests\Unit\Support;

use App\Support\RichTextSanitizer;
use PHPUnit\Framework\TestCase;

class RichTextSanitizerTest extends TestCase
{
    public function test_removes_script_tags(): void
    {
        $html = '<p>Hello</p><script>alert("xss")</script><p>World</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('alert', $result);
        $this->assertStringContainsString('<p>Hello</p>', $result);
        $this->assertStringContainsString('<p>World</p>', $result);
    }

    public function test_removes_inline_event_handlers(): void
    {
        $html = '<p onclick="alert(1)" onload="evil()">Safe text</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('onload', $result);
        $this->assertStringContainsString('Safe text', $result);
    }

    public function test_allows_safe_html_tags(): void
    {
        $html = '<h1>Heading</h1><p><strong>Bold</strong> and <em>italic</em></p>'
            .'<ul><li>One</li></ul><blockquote>Quote</blockquote><pre><code>code()</code></pre>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringContainsString('<h1>Heading</h1>', $result);
        $this->assertStringContainsString('<strong>Bold</strong>', $result);
        $this->assertStringContainsString('<em>italic</em>', $result);
        $this->assertStringContainsString('<ul>', $result);
        $this->assertStringContainsString('<li>One</li>', $result);
        $this->assertStringContainsString('<blockquote>', $result);
        $this->assertStringContainsString('<pre>', $result);
        $this->assertStringContainsString('<code>code()</code>', $result);
    }

    public function test_removes_javascript_urls(): void
    {
        $html = '<a href="javascript:alert(1)">Click me</a><a href="/relative/path">Relative</a><a href="https://example.com">External</a>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringContainsString('href="/relative/path"', $result);
        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('Click me', $result);
    }

    public function test_removes_data_uris(): void
    {
        $html = '<img src="data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=" alt="bad">';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('data:', $result);
    }

    public function test_keeps_relative_and_same_site_image_sources(): void
    {
        $html = '<figure><img src="content/knowledge/2026/08/hero.webp" alt="relative"></figure>'
            .'<img src="/storage/content/sops/2026/08/hero.webp" alt="absolute">'
            .'<img src="//cdn.example.com/banner.webp" alt="protocol-relative">';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringContainsString('src="content/knowledge/2026/08/hero.webp"', $result);
        $this->assertStringContainsString('src="/storage/content/sops/2026/08/hero.webp"', $result);
        $this->assertStringContainsString('src="//cdn.example.com/banner.webp"', $result);
    }

    public function test_strips_dangerous_image_sources(): void
    {
        $html = '<img src="javascript:alert(1)" alt="x">'
            .'<img src="data:image/png;base64,AAAA" alt="y">'
            .'<img src="file:///etc/passwd" alt="z">'
            .'<img src="vbscript:msgbox(1)" alt="w">';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('javascript:', $result);
        $this->assertStringNotContainsString('data:', $result);
        $this->assertStringNotContainsString('file:', $result);
        $this->assertStringNotContainsString('vbscript:', $result);
    }

    public function test_removes_unknown_tags_but_keeps_content(): void
    {
        $html = '<p>Keep <iframe src="https://evil.example"></iframe> this</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<iframe', $result);
        $this->assertStringContainsString('Keep', $result);
        $this->assertStringContainsString('this', $result);
    }

    public function test_removes_script_tags_from_event_handlers_in_attributes(): void
    {
        $html = '<a href="#" onclick="document.location=\'javascript:alert(1)\'">Link</a>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('onclick', $result);
    }

    public function test_renders_markdown_to_sanitized_html(): void
    {
        $markdown = "# Title\n\nA paragraph with **bold** and [a link](https://example.com).\n\n<script>alert(1)</script>";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('<h1>Title</h1>', $result);
        $this->assertStringContainsString('<strong>bold</strong>', $result);
        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringNotContainsString('<script', $result);
    }

    public function test_unclosed_code_fence_does_not_swallow_following_image(): void
    {
        $markdown = "```text\nBefore: x\n\nAfter: y\n\n![alt](https://example.com/a.webp)";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('<img', $result);
        $this->assertStringNotContainsString('![alt]', $result);
    }

    public function test_balanced_code_fence_is_untouched(): void
    {
        $markdown = "```php\necho 'x';\n```\n\nAfter the fence.";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('echo', $result);
        $this->assertStringContainsString('<pre>', $result);
        $this->assertStringContainsString('After the fence.', $result);
    }

    public function test_sanitize_svg_removes_script_and_event_handlers(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script><rect onload="evil()" width="10" height="10"/></svg>';

        $result = RichTextSanitizer::sanitizeSvg($svg);

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('onload', $result);
        $this->assertStringNotContainsString('evil', $result);
    }

    public function test_sanitize_null_returns_empty_string(): void
    {
        $this->assertSame('', RichTextSanitizer::sanitize(null));
        $this->assertSame('', RichTextSanitizer::sanitize(''));
    }

    public function test_renders_markdown_table(): void
    {
        $markdown = "| Rule | Action |\n|---|---|\n| 1-2s | Check control |";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('<th>Rule</th>', $result);
        $this->assertStringContainsString('<th>Action</th>', $result);
        $this->assertStringContainsString('<td>1-2s</td>', $result);
        $this->assertStringContainsString('<td>Check control</td>', $result);
        $this->assertStringContainsString('overflow-x-auto', $result);
    }

    public function test_renders_multi_row_table(): void
    {
        $markdown = "| A | B | C |\n|---|---|---|\n| 1 | 2 | 3 |\n| 4 | 5 | 6 |";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('<td>1</td>', $result);
        $this->assertStringContainsString('<td>5</td>', $result);
        $this->assertStringContainsString('<td>6</td>', $result);
    }

    public function test_table_with_inline_formatting(): void
    {
        $markdown = "| Name | Note |\n|---|---|\n| **Bold** | *Italic* |";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringContainsString('<strong>Bold</strong>', $result);
        $this->assertStringContainsString('<em>Italic</em>', $result);
    }

    public function test_invalid_table_falls_back_to_paragraphs(): void
    {
        $markdown = "| just one row |\n| still no separator |";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringNotContainsString('<table>', $result);
        $this->assertStringContainsString('<p>', $result);
    }

    public function test_table_alignment_center_and_right(): void
    {
        $markdown = "| Left | Center | Right |\n|:---|:---:|---:|\n| a | b | c |";

        $result = RichTextSanitizer::render($markdown);

        $this->assertStringNotContainsString('style="text-align:left"', $result);
        $this->assertStringContainsString('style="text-align:center"', $result);
        $this->assertStringContainsString('style="text-align:right"', $result);
    }
}
