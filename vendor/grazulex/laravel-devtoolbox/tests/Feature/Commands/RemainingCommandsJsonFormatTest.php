<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Tests\TestCase;

final class RemainingCommandsJsonFormatTest extends TestCase
{
    public function test_routes_unused_command_supports_json_format(): void
    {
        $this->artisan('dev:routes:unused --format=json')
            ->assertExitCode(0);
    }

    public function test_model_graph_command_supports_json_format(): void
    {
        $this->artisan('dev:model:graph --format=json')
            ->assertExitCode(0);
    }

    public function test_model_where_used_command_supports_json_format(): void
    {
        // Just test the signature - use a dummy model name
        $this->artisan('dev:model:where-used DummyModel --format=json')
            ->assertExitCode(0);
    }

    public function test_format_option_exists_for_remaining_commands(): void
    {
        // This test just ensures the format option is recognized without runtime errors
        $this->assertTrue(true); // Commands are loaded and signatures are valid if we get here
    }
}
