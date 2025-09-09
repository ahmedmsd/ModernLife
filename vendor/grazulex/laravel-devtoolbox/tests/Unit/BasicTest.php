<?php

declare(strict_types=1);

namespace Tests\Unit;

use Grazulex\LaravelDevtoolbox\LaravelDevtoolboxServiceProvider;
use Orchestra\Testbench\TestCase;

final class BasicTest extends TestCase
{
    public function test_service_provider_is_loaded(): void
    {
        $this->assertNotNull($this->app);
        // Test that the service provider is registered instead of bound
        $providers = $this->app->getLoadedProviders();
        $this->assertArrayHasKey(LaravelDevtoolboxServiceProvider::class, $providers);
    }

    public function test_config_is_published(): void
    {
        $this->assertTrue(config('devtoolbox.status_tracking.enabled', true));
    }

    public function test_package_configuration_is_available(): void
    {
        $this->assertNotNull($this->app);
        // Test that config is properly merged
        $this->assertIsArray(config('devtoolbox'));
        $this->assertArrayHasKey('defaults', config('devtoolbox'));

        // Test defaults config structure
        $defaults = config('devtoolbox.defaults');
        $this->assertIsArray($defaults);
        $this->assertArrayHasKey('format', $defaults);
        $this->assertArrayHasKey('include_metadata', $defaults);
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelDevtoolboxServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup the application environment for testing
        $app['config']->set('devtoolbox.enabled', true);
    }
}
