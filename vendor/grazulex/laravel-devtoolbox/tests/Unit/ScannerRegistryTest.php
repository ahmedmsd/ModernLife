<?php

declare(strict_types=1);

use Grazulex\LaravelDevtoolbox\Registry\ScannerRegistry;
use Grazulex\LaravelDevtoolbox\Scanners\ModelScanner;
use Grazulex\LaravelDevtoolbox\Scanners\RouteScanner;

describe('ScannerRegistry', function (): void {
    test('it can register and retrieve scanners', function (): void {
        $registry = new ScannerRegistry();
        $scanner = new ModelScanner($this->app);

        $registry->register('models', $scanner);

        expect($registry->get('models'))->toBe($scanner);
    });
    test('it throws exception for unregistered scanner', function (): void {
        $registry = new ScannerRegistry();

        expect(fn () => $registry->get('nonexistent'))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it can check if scanner exists', function (): void {
        $registry = new ScannerRegistry();
        $scanner = new RouteScanner($this->app);

        $registry->register('routes', $scanner);

        expect($registry->has('routes'))->toBeTrue();
        expect($registry->has('nonexistent'))->toBeFalse();
    });

    test('it returns all registered scanner names', function (): void {
        $registry = new ScannerRegistry();

        $registry->register('models', new ModelScanner($this->app));
        $registry->register('routes', new RouteScanner($this->app));
        $names = $registry->all();

        expect($names)
            ->toBeArray()
            ->toContain('models')
            ->toContain('routes');
    });

    test('it can unregister a scanner', function (): void {
        $registry = new ScannerRegistry();
        $scanner = new ModelScanner($this->app);

        $registry->register('models', $scanner);
        expect($registry->has('models'))->toBeTrue();

        $registry->unregister('models');
        expect($registry->has('models'))->toBeFalse();
    });
});
