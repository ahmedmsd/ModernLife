<?php

declare(strict_types=1);

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Grazulex\LaravelDevtoolbox\LaravelDevtoolboxServiceProvider;

describe('Devtoolbox Integration', function (): void {
    test('service provider is properly registered', function (): void {
        expect($this->app->getLoadedProviders())
            ->toHaveKey(LaravelDevtoolboxServiceProvider::class);
    });

    test('devtoolbox manager is bound in container', function (): void {
        expect($this->app->bound(DevtoolboxManager::class))->toBeTrue();
        expect($this->app->bound('devtoolbox'))->toBeTrue();
    });

    test('devtoolbox manager can be resolved', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);

        expect($manager)->toBeInstanceOf(DevtoolboxManager::class);
    });

    test('all default scanners are registered', function (): void {
        $manager = $this->app->make(DevtoolboxManager::class);
        $availableScanners = $manager->availableScanners();

        expect($availableScanners)
            ->toContain('models')
            ->toContain('routes')
            ->toContain('commands')
            ->toContain('services')
            ->toContain('middleware')
            ->toContain('views');
    });

    test('config is properly merged', function (): void {
        expect(config('devtoolbox'))->toBeArray();
        expect(config('devtoolbox.defaults'))->toBeArray();
    });
});
