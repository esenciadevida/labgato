<?php

namespace Tests\Feature\Security;

use App\Support\RichTextSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RichTextSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanitizer_removes_script_tags(): void
    {
        $html = '<div>Hello</div><script>alert(1)</script><p><script>evil()</script>World</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('alert(1)', $result);
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
    }

    public function test_sanitizer_removes_inline_event_handlers(): void
    {
        $html = '<img src="https://example.com/x.png" onerror="alert(1)"><p onclick="steal()">Safe</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('onerror', $result);
        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('src="https://example.com/x.png"', $result);
    }

    public function test_sanitizer_removes_javascript_protocol_from_links(): void
    {
        $html = '<a href="javascript:alert(1)">Click</a>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('href=', $result);
        $this->assertStringContainsString('Click', $result);
    }

    public function test_sanitizer_removes_vbscript_and_data_protocols(): void
    {
        $html = '<a href="vbscript:msgbox(1)">VBS</a><a href="data:text/html;base64,PHNjcmlwdD4=">Data</a>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('vbscript:', $result);
        $this->assertStringNotContainsString('data:', $result);
    }

    public function test_sanitizer_neutralizes_obfuscated_javascript_protocol(): void
    {
        $html = '<a href="jav&#x09;ascript:alert(1)">Trick</a>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('href=', $result);
        $this->assertStringContainsString('Trick', $result);
    }

    public function test_sanitizer_removes_form_and_input_tags(): void
    {
        $html = '<form action="https://evil.example"><input name="pw" type="password"></form><p>ok</p>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringNotContainsString('<form', $result);
        $this->assertStringNotContainsString('<input', $result);
        $this->assertStringContainsString('ok', $result);
    }

    public function test_sanitizer_preserves_safe_html_structure(): void
    {
        $html = '<h2>Title</h2><p><strong>Bold</strong> and <em>italic</em></p><ul><li>Item</li></ul>';

        $result = RichTextSanitizer::sanitize($html);

        $this->assertStringContainsString('<h2>Title</h2>', $result);
        $this->assertStringContainsString('<strong>Bold</strong>', $result);
        $this->assertStringContainsString('<em>italic</em>', $result);
        $this->assertStringContainsString('<li>Item</li>', $result);
    }
}
