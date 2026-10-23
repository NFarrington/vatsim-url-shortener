<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\RefreshDatabase;

class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function pages_report_the_policy_by_default()
    {
        $response = $this->get(route('site.home'));

        $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
        $this->assertNull($response->headers->get('Content-Security-Policy'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pages_enforce_the_policy_when_enabled()
    {
        config(['csp.enforce' => true]);

        $response = $this->get(route('site.home'));

        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
        $this->assertNull($response->headers->get('Content-Security-Policy-Report-Only'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function the_policy_restricts_scripts_and_embedding()
    {
        $policy = $this->get(route('site.home'))->headers->get('Content-Security-Policy-Report-Only');

        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("base-uri 'self'", $policy);
        $this->assertStringContainsString("frame-ancestors 'self'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
        $this->assertStringNotContainsString("'unsafe-inline'", $policy);
        $this->assertStringNotContainsString("'unsafe-eval'", $policy);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function inline_scripts_carry_the_nonce_from_the_header()
    {
        $response = $this->get(route('site.home'));

        preg_match("/script-src 'nonce-([^']+)'/", $response->headers->get('Content-Security-Policy-Report-Only'), $matches);
        $nonce = $matches[1] ?? null;

        $this->assertNotEmpty($nonce);
        $content = $response->getContent();
        $this->assertSame(substr_count($content, '<script'), substr_count($content, 'nonce="'.$nonce.'"'));
        $this->assertStringContainsString('<script nonce="'.$nonce.'">(function(w,d,s,l,i)', $content);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function the_nonce_differs_between_requests()
    {
        $nonces = [];
        foreach ([1, 2] as $i) {
            $policy = $this->get(route('site.home'))->headers->get('Content-Security-Policy-Report-Only');
            preg_match("/'nonce-([^']+)'/", $policy, $matches);
            $nonces[] = $matches[1];
        }

        $this->assertNotSame($nonces[0], $nonces[1]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function platform_pages_also_get_the_policy()
    {
        $response = $this->get(route('platform.login'));

        $this->assertNotNull($response->headers->get('Content-Security-Policy-Report-Only'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function pages_have_no_inline_event_handlers_or_style_attributes()
    {
        $this->signIn();

        $content = $this->get(route('platform.dashboard'))->getContent();

        $this->assertDoesNotMatchRegularExpression('/\son\w+="/i', $content);
        $this->assertDoesNotMatchRegularExpression('/\sstyle="/i', $content);
    }
}
