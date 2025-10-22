<?php

declare(strict_types=1);

use Grazulex\LaravelDevtoolbox\Scanners\CommandScanner;
use Grazulex\LaravelDevtoolbox\Scanners\ModelScanner;
use Grazulex\LaravelDevtoolbox\Scanners\RouteScanner;
use Grazulex\LaravelDevtoolbox\Scanners\ServiceScanner;

describe('ModelScanner', function (): void {
    test('it can scan models in a Laravel application', function (): void {
        $scanner = new ModelScanner($this->app);

        $result = $scanner->scan();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it detects model relationships', function (): void {
        $scanner = new ModelScanner($this->app);

        $result = $scanner->scan(['include_relationships' => true]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it can filter models by path', function (): void {
        $scanner = new ModelScanner($this->app);

        $result = $scanner->scan(['paths' => ['app/Models']]);

        expect($result)->toBeArray();
    });
});

describe('RouteScanner', function (): void {
    test('it can scan all registered routes', function (): void {
        $scanner = new RouteScanner($this->app);

        $result = $scanner->scan();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it can detect unused routes', function (): void {
        $scanner = new RouteScanner($this->app);

        $result = $scanner->scan(['detect_unused' => true]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it can group routes by middleware', function (): void {
        $scanner = new RouteScanner($this->app);

        $result = $scanner->scan(['group_by_middleware' => true]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });
});

describe('CommandScanner', function (): void {
    test('it can scan all artisan commands', function (): void {
        $scanner = new CommandScanner($this->app);

        $result = $scanner->scan();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it can filter custom commands only', function (): void {
        $scanner = new CommandScanner($this->app);

        $result = $scanner->scan(['custom_only' => true]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });
});

describe('ServiceScanner', function (): void {
    test('it can scan service container bindings', function (): void {
        $scanner = new ServiceScanner($this->app);

        $result = $scanner->scan();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });

    test('it can detect singleton services', function (): void {
        $scanner = new ServiceScanner($this->app);

        $result = $scanner->scan(['include_singletons' => true]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['metadata', 'data']);
    });
});
