<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Tests\Feature\Commands;

use Route;
use Tests\TestCase;

final class DevSqlTraceCommandTest extends TestCase
{
    public function test_it_requires_route_or_url_option(): void
    {
        $this->artisan('dev:sql:trace')
            ->expectsOutput('Please specify either --route or --url option')
            ->assertExitCode(1);
    }

    public function test_it_can_trace_url(): void
    {
        // Create a simple test route
        Route::get('/test-route', function () {
            return response()->json(['message' => 'test']);
        });

        $this->artisan('dev:sql:trace --url=/test-route')
            ->expectsOutput('Tracing SQL queries for URL \'/test-route\'...')
            ->assertExitCode(0);
    }

    public function test_it_can_handle_invalid_json_parameters(): void
    {
        $this->artisan('dev:sql:trace --url=/test --parameters="invalid-json"')
            ->expectsOutput('Invalid JSON in parameters option')
            ->assertExitCode(1);
    }

    public function test_it_can_handle_invalid_json_headers(): void
    {
        $this->artisan('dev:sql:trace --url=/test --headers="invalid-json"')
            ->expectsOutput('Invalid JSON in headers option')
            ->assertExitCode(1);
    }

    public function test_it_can_save_output_to_file(): void
    {
        $outputFile = storage_path('framework/testing/sql-trace.json');

        // Clean up any existing file
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        // Create a simple test route
        Route::get('/test-sql-trace', function () {
            return response()->json(['message' => 'test']);
        });

        $this->artisan("dev:sql:trace --url=/test-sql-trace --output={$outputFile}")
            ->expectsOutput('Tracing SQL queries for URL \'/test-sql-trace\'...')
            ->expectsOutput("Results saved to: {$outputFile}")
            ->assertExitCode(0);

        $this->assertFileExists($outputFile);

        $content = file_get_contents($outputFile);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);

        // Clean up
        unlink($outputFile);
    }

    public function test_it_can_handle_different_http_methods(): void
    {
        // Create test routes for different methods
        Route::post('/test-post', function () {
            return response()->json(['method' => 'POST']);
        });

        $this->artisan('dev:sql:trace --url=/test-post --method=POST')
            ->expectsOutput('Tracing SQL queries for URL \'/test-post\'...')
            ->assertExitCode(0);
    }
}
