<?php

namespace Tests\Unit;

use App\Services\BlogHtmlSanitizer;
use Tests\TestCase;

class BlogHtmlSanitizerTest extends TestCase
{
    public function test_it_strips_script_tags_and_inner_json(): void
    {
        $html = '<p>Hola</p><script type="application/ld+json">{"@type":"FAQPage"}</script>';

        $result = (new BlogHtmlSanitizer())->sanitize($html);

        $this->assertSame('<p>Hola</p>', $result);
        $this->assertStringNotContainsString('FAQPage', $result);
    }

    public function test_it_strips_cdata_markers(): void
    {
        $html = '<![CDATA[<p>Texto</p>]]>';

        $result = (new BlogHtmlSanitizer())->sanitize($html);

        $this->assertSame('<p>Texto</p>', $result);
        $this->assertStringNotContainsString(']]>', $result);
    }
}
