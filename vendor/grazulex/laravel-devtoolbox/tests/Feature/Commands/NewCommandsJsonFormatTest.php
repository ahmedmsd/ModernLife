<?php

declare(strict_types=1);

namespace Tests\Feature\Commands;

use Tests\TestCase;

final class NewCommandsJsonFormatTest extends TestCase
{
    public function test_db_column_usage_command_supports_json_format(): void
    {
        $this->artisan('dev:db:column-usage --format=json')
            ->assertExitCode(0);
    }

    public function test_security_unprotected_routes_command_supports_json_format(): void
    {
        $this->artisan('dev:security:unprotected-routes --format=json')
            ->assertExitCode(0);
    }

    public function test_dev_scan_all_command_supports_json_format(): void
    {
        $this->artisan('dev:scan --all --format=json')
            ->assertExitCode(0);
    }
}
