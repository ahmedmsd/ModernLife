<?php

declare(strict_types=1);

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;

describe('Scanner Functionality', function (): void {
    test('can scan models', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('models');

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data'])
            ->and($result['type'])->toBe('models');
    });

    test('can scan routes', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('routes');

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data'])
            ->and($result['type'])->toBe('routes');
    });

    test('can scan commands', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('commands');

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data'])
            ->and($result['type'])->toBe('commands');
    });

    test('can scan services', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('services');

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data'])
            ->and($result['type'])->toBe('services');
    });

    test('can scan multiple types at once', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scanMultiple(['models', 'routes']);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['timestamp', 'scanned_types', 'results'])
            ->and($result['scanned_types'])->toBe(['models', 'routes'])
            ->and($result['results'])->toHaveKeys(['models', 'routes']);
    });

    test('can scan all available types', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scanAll();

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['timestamp', 'scanned_types', 'results']);

        $availableScanners = $manager->availableScanners();
        expect($result['scanned_types'])->toBe($availableScanners);
    });

    test('throws exception for unknown scanner type', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        expect(fn () => $manager->scan('unknown_type'))
            ->toThrow(InvalidArgumentException::class, 'No scanner registered for type [unknown_type].');
    });

    test('can pass options to scanners', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        $options = ['include_metadata' => false];
        $result = $manager->scan('models', $options);

        expect($result['options'])->toBe($options);
    });
});
