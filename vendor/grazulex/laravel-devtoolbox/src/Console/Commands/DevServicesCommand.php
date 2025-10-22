<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevServicesCommand extends Command
{
    protected $signature = 'dev:services {--format=table : Output format (table, json)} {--output= : Output file path}';

    protected $description = 'Scan and list all registered services';

    public function handle(DevtoolboxManager $manager): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info('Scanning registered services...');
        }

        try {
            $result = $manager->scan('services', [
                'format' => $format,
            ]);

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
        } catch (Exception $e) {
            $this->error('Error scanning services: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data']['services'] ?? [];
        $this->line('Found '.count($data).' services:');
        $this->newLine();

        foreach ($data as $service) {
            $this->line("🔧 {$service['abstract']}");
            if (isset($service['concrete'])) {
                $this->line("   → {$service['concrete']}");
            }
            $this->newLine();
        }
    }
}
