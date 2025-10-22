<?php

declare(strict_types=1);

namespace Tests\Unit;

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Grazulex\LaravelDevtoolbox\Scanners\ModelScanner;
use Grazulex\LaravelDevtoolbox\Scanners\RouteScanner;
use Grazulex\LaravelDevtoolbox\Scanners\ServiceScanner;
use InvalidArgumentException;
use Tests\TestCase;

final class ScannersUnitTest extends TestCase
{
    public function test_route_scanner_can_scan_empty_routes(): void
    {
        $scanner = new RouteScanner($this->app);
        $result = $scanner->scan();

        $this->assertIsArray($result);
        // Test that scanner returns a structured result
        $this->assertNotEmpty($result);
    }

    public function test_model_scanner_can_scan_with_empty_models(): void
    {
        $scanner = new ModelScanner($this->app);
        $result = $scanner->scan();

        $this->assertIsArray($result);
        // Test that scanner returns a structured result
        $this->assertNotEmpty($result);
    }

    public function test_service_scanner_can_scan_container(): void
    {
        $scanner = new ServiceScanner($this->app);
        $result = $scanner->scan();

        $this->assertIsArray($result);
        // Test that scanner returns a structured result
        $this->assertNotEmpty($result);
    }

    public function test_devtoolbox_manager_can_handle_all_scanner_types(): void
    {
        $manager = app(DevtoolboxManager::class);

        $scannerTypes = [
            'models',
            'routes',
            'commands',
            'services',
            'middleware',
            'views',
        ];

        foreach ($scannerTypes as $type) {
            $result = $manager->scan($type);
            $this->assertIsArray($result);
        }
    }

    public function test_devtoolbox_manager_handles_invalid_scanner_gracefully(): void
    {
        $manager = app(DevtoolboxManager::class);

        $this->expectException(InvalidArgumentException::class);
        $manager->scan('invalid_scanner_type');
    }

    public function test_scanners_return_consistent_structure(): void
    {
        $manager = app(DevtoolboxManager::class);

        $result = $manager->scan('models');
        $this->assertIsArray($result);

        $result = $manager->scan('routes');
        $this->assertIsArray($result);

        $result = $manager->scan('services');
        $this->assertIsArray($result);
    }
}
