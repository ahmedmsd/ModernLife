<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Registry;

use Grazulex\LaravelDevtoolbox\Contracts\ScannerInterface;
use InvalidArgumentException;

final class ScannerRegistry
{
    /** @var array<string, ScannerInterface> */
    private array $scanners = [];

    /**
     * Register a scanner
     */
    public function register(string $name, ScannerInterface $scanner): void
    {
        $this->scanners[$name] = $scanner;
    }

    /**
     * Get a scanner by name
     */
    public function get(string $name): ScannerInterface
    {
        if (! $this->has($name)) {
            throw new InvalidArgumentException("No scanner registered for type [{$name}].");
        }

        return $this->scanners[$name];
    }

    /**
     * Check if a scanner exists
     */
    public function has(string $name): bool
    {
        return isset($this->scanners[$name]);
    }

    /**
     * Get all registered scanner names
     */
    public function all(): array
    {
        return array_keys($this->scanners);
    }

    /**
     * Unregister a scanner
     */
    public function unregister(string $name): void
    {
        unset($this->scanners[$name]);
    }

    /**
     * Get all scanners
     */
    public function getScanners(): array
    {
        return $this->scanners;
    }

    /**
     * Clear all scanners
     */
    public function clear(): void
    {
        $this->scanners = [];
    }
}
