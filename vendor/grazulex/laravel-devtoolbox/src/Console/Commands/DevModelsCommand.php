<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevModelsCommand extends Command
{
    protected $signature = 'dev:models {--format=table : Output format (table, json)} {--output= : Output file path}';

    protected $description = 'Scan and list all Eloquent models';

    public function handle(DevtoolboxManager $manager): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info('Scanning Eloquent models...');
        }

        try {
            $result = $manager->scan('models', [
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
            $this->error('Error scanning models: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data'] ?? [];
        $this->line('Found '.count($data).' models:');
        $this->newLine();

        foreach ($data as $model) {
            $className = $model['full_class'] ?? $model['name'] ?? 'Unknown';
            $this->line("📄 {$className}");
            if (isset($model['file_path'])) {
                $this->line("   File: {$model['file_path']}");
            }
            if (isset($model['relationships']) && ! empty($model['relationships'])) {
                $this->line('   Relationships: '.implode(', ', array_keys($model['relationships'])));
            }
            $this->newLine();
        }
    }
}
