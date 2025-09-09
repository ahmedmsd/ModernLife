<?php

declare(strict_types=1);

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Grazulex\LaravelDevtoolbox\Registry\ScannerRegistry;
use Grazulex\LaravelDevtoolbox\Scanners\CommandScanner;
use Grazulex\LaravelDevtoolbox\Scanners\ModelScanner;
use Grazulex\LaravelDevtoolbox\Scanners\RouteScanner;
use Grazulex\LaravelDevtoolbox\Scanners\ServiceScanner;

describe('DevtoolboxManager', function (): void {
    test('it initializes with all default scanners registered', function (): void {
        $manager = new DevtoolboxManager;
        $registry = $manager->registry();

        expect($registry->get('models'))->toBeInstanceOf(ModelScanner::class);
        expect($registry->get('commands'))->toBeInstanceOf(CommandScanner::class);
        expect($registry->get('routes'))->toBeInstanceOf(RouteScanner::class);
        expect($registry->get('services'))->toBeInstanceOf(ServiceScanner::class);
    });

    test('it can scan with registered scanner', function (): void {
        $manager = new DevtoolboxManager;

        $result = $manager->scan('models');

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data'])
            ->and($result['type'])->toBe('models')
            ->and($result['metadata'])->toBeArray()
            ->and($result['data'])->toBeArray();
    });

    test('it throws exception for unknown scanner type', function (): void {
        $manager = new DevtoolboxManager;

        expect(fn (): array => $manager->scan('unknown_type'))
            ->toThrow(InvalidArgumentException::class, 'No scanner registered for type [unknown_type].');
    });

    test('it can scan with options', function (): void {
        $manager = new DevtoolboxManager;

        $result = $manager->scan('models', ['paths' => [__DIR__.'/../../Fixtures']]);

        expect($result)
            ->toBeArray()
            ->toHaveKeys(['type', 'timestamp', 'options', 'metadata', 'data']);
    });

    test('it returns scanner registry', function (): void {
        $manager = new DevtoolboxManager;

        expect($manager->registry())->toBeInstanceOf(ScannerRegistry::class);
    });
});
