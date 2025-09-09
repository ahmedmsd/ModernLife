<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevScanCommand extends Command
{
    protected $signature = 'dev:scan 
                            {type? : The type of scan to perform (models, routes, commands, services, middleware, views)}
                            {--all : Scan all available types}
                            {--format=table : Output format (table, json)}
                            {--output= : Save output to file}';

    protected $description = 'Scan and analyze various aspects of your Laravel application';

    public function handle(DevtoolboxManager $manager): int
    {
        $type = $this->argument('type');
        $all = $this->option('all');
        $format = $this->option('format');
        $output = $this->option('output');

        if ($all) {
            // Only show progress message if not outputting JSON directly
            if ($format !== 'json') {
                $this->info('Scanning all available types...');
            }
            $result = $manager->scanAll(['format' => $format]);
        } elseif ($type) {
            if (! in_array($type, $manager->availableScanners())) {
                $this->error("Unknown scanner type: {$type}");
                $this->line('Available types: '.implode(', ', $manager->availableScanners()));

                return self::FAILURE;
            }

            // Only show progress message if not outputting JSON directly
            if ($format !== 'json') {
                $this->info("Scanning {$type}...");
            }
            $result = $manager->scan($type, ['format' => $format]);
        } else {
            $this->info('Available scanner types:');
            foreach ($manager->availableScanners() as $scanner) {
                $this->line("  - {$scanner}");
            }

            return self::SUCCESS;
        }

        if ($output) {
            file_put_contents($output, json_encode($result, JSON_PRETTY_PRINT));
            $this->info("Results saved to: {$output}");
        } elseif ($format === 'json') {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->displayResults($result);
        }

        return self::SUCCESS;
    }

    private function displayResults(array $result): void
    {
        foreach ($result as $type => $data) {
            $this->info("=== {$type} ===");

            if (empty($data)) {
                $this->line('No items found.');

                continue;
            }

            if (is_array($data) && isset($data[0]) && is_array($data[0])) {
                // Tabular data
                $headers = array_keys($data[0]);
                $this->table($headers, $data);
            } elseif (is_array($data)) {
                // Simple list
                foreach ($data as $item) {
                    if (is_string($item)) {
                        $this->line("  {$item}");
                    } else {
                        $this->line('  '.json_encode($item));
                    }
                }
            } else {
                // Single value
                $this->line("  {$data}");
            }

            $this->newLine();
        }
    }
}
