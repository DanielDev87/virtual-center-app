<?php

namespace Tests\Unit;

use App\Http\Controllers\ReportsController;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Tests\TestCase;

class ReportsControllerUnitTest extends TestCase
{
    private function invokePrivate(object $instance, string $method, array $args = [])
    {
        $ref = new \ReflectionClass($instance);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);

        return $m->invokeArgs($instance, $args);
    }

    /** @test */
    public function resolve_export_format_accepts_csv_case_insensitive()
    {
        $controller = new ReportsController();
        $request = Request::create('/admin/reports/tickets', 'GET', ['format' => 'CSV']);

        $result = $this->invokePrivate($controller, 'resolveExportFormat', [$request]);

        $this->assertSame('csv', $result);
    }

    /** @test */
    public function resolve_export_format_accepts_pdf_format()
    {
        $controller = new ReportsController();
        $request = Request::create('/admin/reports/tickets', 'GET', ['format' => 'pdf']);

        $result = $this->invokePrivate($controller, 'resolveExportFormat', [$request]);

        $this->assertSame('pdf', $result);
    }

    /** @test */
    public function clean_filters_removes_null_and_empty_string_but_keeps_zero_values()
    {
        $controller = new ReportsController();

        $result = $this->invokePrivate($controller, 'cleanFilters', [[
            'a' => null,
            'b' => '',
            'c' => 0,
            'd' => '0',
            'e' => 'valor',
        ]]);

        $this->assertArrayNotHasKey('a', $result);
        $this->assertArrayNotHasKey('b', $result);
        $this->assertSame(0, $result['c']);
        $this->assertSame('0', $result['d']);
        $this->assertSame('valor', $result['e']);
    }

    /** @test */
    public function ensure_filters_applied_returns_redirect_when_all_filters_are_missing()
    {
        $controller = new ReportsController();
        $request = Request::create('/admin/reports/tickets', 'GET');

        $result = $this->invokePrivate($controller, 'ensureFiltersApplied', [$request, ['status', 'priority']]);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringContainsString('/admin/reports', $result->getTargetUrl());
    }

    /** @test */
    public function ensure_filters_applied_returns_null_when_a_filter_with_zero_string_is_present()
    {
        $controller = new ReportsController();
        $request = Request::create('/admin/reports/collaborators', 'GET', ['is_active' => '0']);

        $result = $this->invokePrivate($controller, 'ensureFiltersApplied', [$request, ['is_active']]);

        $this->assertNull($result);
    }
}
