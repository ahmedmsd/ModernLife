<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Illuminate\Console\Command;

final class DevEnvDiffCommand extends Command
{
    protected $signature = 'dev:env:diff 
                            {--against=.env.example : Compare against this file}
                            {--format=table : Output format (table, json)}
                            {--output= : Save output to file}';

    protected $description = 'Compare environment configuration files';

    public function handle(): int
    {
        $against = $this->option('against');
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info("Comparing .env with {$against}...");
        }

        $envFile = base_path('.env');
        $compareFile = base_path($against);

        if (! file_exists($envFile)) {
            $this->error('.env file not found');

            return self::FAILURE;
        }

        if (! file_exists($compareFile)) {
            $this->error("Comparison file {$against} not found");

            return self::FAILURE;
        }

        $envVars = $this->parseEnvFile($envFile);
        $compareVars = $this->parseEnvFile($compareFile);

        $result = [
            'missing_in_env' => array_diff_key($compareVars, $envVars),
            'missing_in_compare' => array_diff_key($envVars, $compareVars),
            'different_values' => [],
            'scanned_at' => now()->toISOString(),
        ];

        // Check for different values
        foreach ($envVars as $key => $value) {
            if (isset($compareVars[$key]) && $compareVars[$key] !== $value) {
                $result['different_values'][$key] = [
                    'env' => $value,
                    'compare' => $compareVars[$key],
                ];
            }
        }

        if ($output) {
            file_put_contents($output, json_encode($result, JSON_PRETTY_PRINT));
            if ($format !== 'json') {
                $this->info("Results saved to: {$output}");
            }
        } elseif ($format === 'json') {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->displayResults($result);
        }

        return self::SUCCESS;
    }

    private function parseEnvFile(string $filePath): array
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $vars = [];

        foreach ($lines as $line) {
            if (str_starts_with(mb_trim($line), '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $vars[mb_trim($key)] = mb_trim($value);
            }
        }

        return $vars;
    }

    private function displayResults(array $result): void
    {
        if (empty($result['missing_in_example']) && empty($result['missing_in_env']) && empty($result['value_differences'])) {
            $this->info('✅ Environment files are in sync!');

            return;
        }

        if (! empty($result['missing_in_example'])) {
            $this->warn('Missing in .env.example:');
            foreach ($result['missing_in_example'] as $key) {
                $this->line("  - {$key}");
            }
            $this->newLine();
        }

        if (! empty($result['missing_in_env'])) {
            $this->warn('Missing in .env:');
            foreach ($result['missing_in_env'] as $key) {
                $this->line("  - {$key}");
            }
            $this->newLine();
        }

        if (! empty($result['value_differences'])) {
            $this->warn('Value differences:');
            foreach ($result['value_differences'] as $diff) {
                $this->line("  {$diff['key']}:");
                $this->line("    .env: {$diff['env_value']}");
                $this->line("    .env.example: {$diff['example_value']}");
            }
        }
    }
}
