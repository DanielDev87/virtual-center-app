<?php

namespace Tests\Unit;

use App\Http\Controllers\ServiceManagementController;
use Tests\TestCase;

class ServiceManagementControllerSecurityUnitTest extends TestCase
{
    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $reflection = new \ReflectionClass($instance);
        $targetMethod = $reflection->getMethod($method);
        $targetMethod->setAccessible(true);

        return $targetMethod->invokeArgs($instance, $args);
    }

    /** @test */
    public function sanitize_rich_text_removes_unsafe_content_but_keeps_allowed_tags()
    {
        $controller = new ServiceManagementController();

        $input = '<p onclick="alert(1)">Hola <strong>Mundo</strong></p><script>alert(2)</script><img src="javascript:alert(3)" onerror="alert(4)">';

        $sanitized = $this->invokePrivate($controller, 'sanitizeRichText', [$input]);

        $this->assertStringNotContainsString('<script', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
        $this->assertStringNotContainsString('onerror=', $sanitized);
        $this->assertStringNotContainsString('javascript:', strtolower($sanitized));
        $this->assertMatchesRegularExpression('/<p\b[^>]*>/', $sanitized);
        $this->assertStringContainsString('<strong>Mundo</strong>', $sanitized);
    }

    /** @test */
    public function decode_embedded_image_data_uri_returns_expected_payload_for_supported_mime()
    {
        $controller = new ServiceManagementController();
        $payload = base64_encode('abc');

        $decoded = $this->invokePrivate(
            $controller,
            'decodeEmbeddedImageDataUri',
            ["data:image/png;base64,{$payload}"]
        );

        $this->assertIsArray($decoded);
        $this->assertSame('image/png', $decoded['mime']);
        $this->assertSame('png', $decoded['extension']);
        $this->assertSame('abc', $decoded['binary']);
    }

    /** @test */
    public function decode_embedded_image_data_uri_rejects_invalid_or_unsupported_sources()
    {
        $controller = new ServiceManagementController();

        $invalidFormat = $this->invokePrivate($controller, 'decodeEmbeddedImageDataUri', ['not-a-data-uri']);
        $unsupportedMime = $this->invokePrivate($controller, 'decodeEmbeddedImageDataUri', ['data:image/bmp;base64,' . base64_encode('abc')]);

        $this->assertNull($invalidFormat);
        $this->assertNull($unsupportedMime);
    }

    /** @test */
    public function is_safe_image_source_accepts_only_relative_or_http_sources()
    {
        $controller = new ServiceManagementController();

        $this->assertTrue($this->invokePrivate($controller, 'isSafeImageSource', ['/storage/img.png']));
        $this->assertTrue($this->invokePrivate($controller, 'isSafeImageSource', ['http://example.com/a.png']));
        $this->assertTrue($this->invokePrivate($controller, 'isSafeImageSource', ['https://example.com/a.png']));

        $this->assertFalse($this->invokePrivate($controller, 'isSafeImageSource', ['']));
        $this->assertFalse($this->invokePrivate($controller, 'isSafeImageSource', ['javascript:alert(1)']));
        $this->assertFalse($this->invokePrivate($controller, 'isSafeImageSource', ['data:image/png;base64,abc']));
    }
}
