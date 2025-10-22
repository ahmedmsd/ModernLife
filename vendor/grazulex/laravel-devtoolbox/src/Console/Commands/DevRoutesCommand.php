<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevRoutesCommand extends Command
{
    protected $signature = 'dev:routes {--format=table : Output format (table, json)} {--output= : Output file path}';

    protected $description = 'Scan and list all application routes';

    public function handle(DevtoolboxManager $manager): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info('Scanning application routes...');
        }

        try {
            $result = $manager->scan('routes', [
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
            $this->error('Error scanning routes: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data']['routes'] ?? [];
        $this->line('Found '.count($data).' routes:');
        $this->newLine();

        foreach ($data as $route) {
            $methods = implode('|', $route['methods'] ?? ['GET']);
            $this->line("🛣️  {$methods} {$route['uri']}");
            if (isset($route['name'])) {
                $this->line("   Name: {$route['name']}");
            }
            if (isset($route['action'])) {
                $this->line("   Action: {$route['action']}");
            }
            $this->newLine();
        }
    }
}
