<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Contracts;

interface ScannerInterface
{
    /**
     * Scan and return results
     */
    public function scan(array $options = []): array;

    /**
     * Get the scanner name
     */
    public function getName(): string;

    /**
     * Get the scanner description
     */
    public function getDescription(): string;

    /**
     * Get available options for this scanner
     */
    public function getAvailableOptions(): array;
}
