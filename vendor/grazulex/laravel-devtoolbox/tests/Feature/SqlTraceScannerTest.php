<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Tests\Feature;

use DB;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Route;
use Tests\TestCase;

final class SqlTraceScannerTest extends TestCase
{
    public function test_sql_trace_scanner_is_registered(): void
    {
        $manager = $this->app->make(DevtoolboxManager::class);

        $scanners = $manager->availableScanners();

        $this->assertContains('sql-trace', $scanners);
    }

    public function test_sql_trace_scanner_requires_route_or_url(): void
    {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('sql-trace', []);

        $this->assertArrayHasKey('data', $result);
        $data = $result['data'];

        // Should return empty structure when no route/url provided
        $this->assertNull($data['traced_target']);
        $this->assertEquals(0, $data['total_queries']);
        $this->assertEmpty($data['queries']);
    }

    public function test_sql_trace_scanner_can_trace_url(): void
    {
        // Create a simple test route
        Route::get('/test-scanner', function () {
            // Simulate some database interaction
            DB::table('non_existent_table')->where('id', 1)->toSql();

            return response()->json(['message' => 'test']);
        });

        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('sql-trace', [
            'url' => '/test-scanner',
            'method' => 'GET',
        ]);

        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('sql-trace', $result['type']);

        $data = $result['data'];
        $this->assertArrayHasKey('traced_target', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('queries', $data);
        $this->assertArrayHasKey('total_queries', $data);
        $this->assertArrayHasKey('total_time', $data);

        $this->assertEquals('/test-scanner', $data['traced_target']);
        $this->assertEquals('GET', $data['method']);
    }

    public function test_sql_trace_scanner_handles_nonexistent_route(): void
    {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('sql-trace', [
            'route' => 'nonexistent.route',
        ]);

        $this->assertArrayHasKey('data', $result);
        $data = $result['data'];

        // Should have an error in the result
        $this->assertArrayHasKey('error', $data);
        $this->assertStringContainsString('Route \'nonexistent.route\' not found', $data['error']);
    }
}
