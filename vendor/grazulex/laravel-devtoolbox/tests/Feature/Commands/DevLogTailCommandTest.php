<?php

declare(strict_types=1);

it('dev log tail command is registered', function () {
    // Simple test to verify the command is registered
    $this->artisan('dev:log:tail', ['--help'])
        ->assertExitCode(0);
});

it('dev log tail command validates file existence', function () {
    // Test with non-existent file
    $this->artisan('dev:log:tail', [
        '--file' => 'nonexistent.log',
        '--lines' => 5,
    ])
        ->assertExitCode(1);
});
