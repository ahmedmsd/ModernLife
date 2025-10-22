<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Tests\Feature\Commands;

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Tests\TestCase;

final class DevModelWhereUsedCommandTest extends TestCase
{
    public function test_it_can_analyze_model_usage(): void
    {
        $this->artisan('dev:model:where-used User')
            ->expectsOutput('Analyzing usage of model: User')
            ->assertExitCode(0);
    }

    public function test_it_can_save_output_to_file(): void
    {
        $outputFile = storage_path('framework/testing/model-usage.json');

        // Clean up any existing file
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        $this->artisan('dev:model:where-used User --output='.$outputFile)
            ->expectsOutput('Analyzing usage of model: User')
            ->expectsOutput('Results saved to: '.$outputFile)
            ->assertExitCode(0);

        $this->assertFileExists($outputFile);

        $content = file_get_contents($outputFile);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertArrayHasKey('data', $json);

        // Clean up
        unlink($outputFile);
    }

    public function test_it_can_handle_different_formats(): void
    {
        // JSON format should NOT show progress message
        $this->artisan('dev:model:where-used User --format=json')
            ->assertExitCode(0);

        // Table format (the default behavior) should show progress message
        $this->artisan('dev:model:where-used User --format=table')
            ->expectsOutput('Analyzing usage of model: User')
            ->assertExitCode(0);
    }

    public function test_it_integrates_with_devtoolbox_manager(): void
    {
        $manager = $this->app->make(DevtoolboxManager::class);

        $result = $manager->scan('model-usage', ['model' => 'User']);

        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('model-usage', $result['type']);

        $data = $result['data'];
        $this->assertArrayHasKey('model', $data);
        $this->assertArrayHasKey('usage', $data);
        $this->assertEquals('App\\Models\\User', $data['model']);
    }
}
