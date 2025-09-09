<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Tests\TestCase;

final class LegacyCommandsJsonFormatTest extends TestCase
{
    public function test_models_command_supports_json_format(): void
    {
        $this->artisan('dev:models --format=json')
            ->assertExitCode(0);
    }

    public function test_routes_command_supports_json_format(): void
    {
        $this->artisan('dev:routes --format=json')
            ->assertExitCode(0);
    }

    public function test_services_command_supports_json_format(): void
    {
        $this->artisan('dev:services --format=json')
            ->assertExitCode(0);
    }

    public function test_commands_command_supports_json_format(): void
    {
        $this->artisan('dev:commands --format=json')
            ->assertExitCode(0);
    }

    public function test_middleware_command_supports_json_format(): void
    {
        $this->artisan('dev:middleware --format=json')
            ->assertExitCode(0);
    }

    public function test_views_command_supports_json_format(): void
    {
        $this->artisan('dev:views --format=json')
            ->assertExitCode(0);
    }
}
