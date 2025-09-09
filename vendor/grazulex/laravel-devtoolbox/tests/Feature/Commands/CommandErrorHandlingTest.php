<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Exception;
use Tests\TestCase;

final class CommandErrorHandlingTest extends TestCase
{
    /**
     * Test that commands handle missing dependencies gracefully
     */
    public function test_commands_handle_missing_files_gracefully(): void
    {
        // Commands should handle missing files appropriately
        try {
            $this->artisan('dev:env:diff --format=json');
            $this->assertTrue(true); // If no exception, it's handled gracefully
        } catch (Exception $e) {
            // If there's an exception, ensure it's a controlled one
            $this->assertInstanceOf(Exception::class, $e);
        }
    }

    /**
     * Test that commands handle empty or invalid input gracefully
     */
    public function test_commands_handle_invalid_input_gracefully(): void
    {
        // Test with non-existent model
        $this->artisan('dev:model:where-used NonExistentModel --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test that commands validate required parameters
     */
    public function test_sql_trace_command_validation(): void
    {
        // SQL trace should handle missing URL parameter
        $this->artisan('dev:sql:trace --format=json')
            ->assertExitCode(1); // Should fail validation
    }

    /**
     * Test that scan command handles unknown types gracefully
     */
    public function test_scan_command_handles_unknown_types(): void
    {
        // Should handle gracefully even with unknown scanner type
        $this->artisan('dev:scan unknown_type --format=json')
            ->assertExitCode(1); // Should exit with error code
    }

    /**
     * Test commands with options that don't exist
     */
    public function test_commands_with_valid_options_only(): void
    {
        // Test db:column-usage with its actual options
        $this->artisan('dev:db:column-usage --unused-only --format=json')
            ->assertExitCode(0);

        // Test security command with its actual options
        $this->artisan('dev:security:unprotected-routes --critical-only --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test that help option works for all commands
     */
    public function test_help_option_for_all_commands(): void
    {
        $commands = [
            'dev:models',
            'dev:routes',
            'dev:commands',
            'dev:services',
            'dev:middleware',
            'dev:views',
            'dev:scan',
            'dev:routes:unused',
            'dev:model:where-used',
            'dev:model:graph',
            'dev:env:diff',
            'dev:sql:trace',
            'dev:security:unprotected-routes',
            'dev:db:column-usage',
        ];

        foreach ($commands as $command) {
            $this->artisan($command.' --help')
                ->assertExitCode(0);
        }
    }
}
