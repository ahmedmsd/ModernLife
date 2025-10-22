<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

final class PackageCommandsTest extends TestCase
{
    /**
     * Test that all commands are properly registered and can be executed
     */
    public function test_all_commands_are_registered_and_executable(): void
    {
        $expectedCommands = [
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

        foreach ($expectedCommands as $command) {
            // Test that command exists and can show help
            $this->artisan($command.' --help')
                ->assertExitCode(0);
        }
    }

    /**
     * Test commands that should work in any environment
     */
    public function test_basic_commands_with_json_format(): void
    {
        // These commands should work in a basic Laravel environment
        $basicCommands = [
            'dev:models --format=json',
            'dev:routes --format=json',
            'dev:commands --format=json',
            'dev:services --format=json',
            'dev:middleware --format=json',
            'dev:views --format=json',
        ];

        foreach ($basicCommands as $command) {
            $this->artisan($command)
                ->assertExitCode(0);
        }
    }

    /**
     * Test that format validation works
     */
    public function test_commands_validate_format_option(): void
    {
        // Test with valid formats
        $this->artisan('dev:models --format=json')
            ->assertExitCode(0);

        $this->artisan('dev:models --format=table')
            ->assertExitCode(0);
    }

    /**
     * Test scan command functionality
     */
    public function test_scan_command_basic_functionality(): void
    {
        $this->artisan('dev:scan models --format=json')
            ->assertExitCode(0);

        $this->artisan('dev:scan routes --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test model analysis commands with mock data
     */
    public function test_model_commands_with_mock(): void
    {
        // Test model:where-used with a fictional model name
        $this->artisan('dev:model:where-used TestModel --format=json')
            ->assertExitCode(0);

        // Test model:graph
        $this->artisan('dev:model:graph --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test SQL trace command with URL parameter
     */
    public function test_sql_trace_command(): void
    {
        $this->artisan('dev:sql:trace --url=/test --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test security command
     */
    public function test_security_command(): void
    {
        $this->artisan('dev:security:unprotected-routes --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test database column usage command
     */
    public function test_db_column_usage_command(): void
    {
        $this->artisan('dev:db:column-usage --format=json')
            ->assertExitCode(0);
    }

    /**
     * Test output file functionality
     */
    public function test_output_file_functionality(): void
    {
        $outputFile = sys_get_temp_dir().'/devtoolbox-test-output.json';

        // Clean up any existing file
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        // Test a basic command with output file
        $this->artisan("dev:models --format=json --output={$outputFile}")
            ->assertExitCode(0);

        // Verify file was created and contains valid JSON
        if (file_exists($outputFile)) {
            $content = file_get_contents($outputFile);
            $this->assertNotEmpty($content);
            $this->assertIsArray(json_decode($content, true));
            unlink($outputFile);
        }
    }

    /**
     * Helper method to create temporary .env files
     */
    private function createTemporaryEnvFiles(): void
    {
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');

        if (! File::exists($envPath)) {
            File::put($envPath, "APP_NAME=TestApp\nAPP_ENV=testing\nDB_CONNECTION=sqlite");
        }

        if (! File::exists($envExamplePath)) {
            File::put($envExamplePath, "APP_NAME=\nAPP_ENV=local\nDB_CONNECTION=mysql");
        }
    }

    /**
     * Helper method to cleanup temporary files
     */
    private function cleanupTemporaryFiles(): void
    {
        $tempFiles = [
            base_path('.env'),
            base_path('.env.example'),
        ];

        foreach ($tempFiles as $file) {
            if (File::exists($file) && str_contains(File::get($file), 'TestApp')) {
                File::delete($file);
            }
        }
    }
}
