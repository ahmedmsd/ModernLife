<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevModelWhereUsedCommand extends Command
{
    protected $signature = 'dev:model:where-used 
                            {model : The model class name or path}
                            {--format=table : Output format (table, json)}
                            {--output= : Save output to file}';

    protected $description = 'Find where a specific model is used throughout the application';

    public function handle(DevtoolboxManager $manager): int
    {
        $model = $this->argument('model');
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info("Analyzing usage of model: {$model}");
        }

        try {
            // Use the specialized ModelUsageScanner
            $result = $manager->scan('model-usage', [
                'model' => $model,
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
            $this->error('Error analyzing model usage: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data'] ?? [];
        $model = $data['model'] ?? 'Unknown';
        $usage = $data['usage'] ?? [];

        $this->line("Model: <info>{$model}</info>");
        $this->line('');

        foreach ($usage as $category => $items) {
            if (! empty($items)) {
                $this->line('<comment>'.ucfirst($category).':</comment>');

                foreach ($items as $item) {
                    $this->line("  📁 {$item['file']}");

                    if (isset($item['usages'])) {
                        foreach ($item['usages'] as $usage) {
                            $this->line("    Line {$usage['line']}: {$usage['type']} - {$usage['code']}");
                        }
                    }

                    if (isset($item['relationships'])) {
                        foreach ($item['relationships'] as $relationship) {
                            $this->line("    Line {$relationship['line']}: {$relationship['type']} - {$relationship['code']}");
                        }
                    }
                }
                $this->line('');
            }
        }
    }
}
