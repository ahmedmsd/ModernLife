<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevSqlTraceCommand extends Command
{
    protected $signature = 'dev:sql:trace 
                            {--route= : Trace SQL for a specific route}
                            {--url= : Trace SQL for a specific URL}
                            {--method=GET : HTTP method to use}
                            {--parameters= : Route/query parameters as JSON}
                            {--headers= : Request headers as JSON}
                            {--format=table : Output format (table, json)}
                            {--output= : Save output to file}';

    protected $description = 'Trace SQL queries executed during route execution';

    public function handle(DevtoolboxManager $manager): int
    {
        $route = $this->option('route');
        $url = $this->option('url');
        $method = $this->option('method');
        $parameters = $this->option('parameters');
        $headers = $this->option('headers');
        $format = $this->option('format');
        $output = $this->option('output');

        if (! $route && ! $url) {
            $this->error('Please specify either --route or --url option');

            return self::FAILURE;
        }

        $target = $route ? "route '{$route}'" : "URL '{$url}'";

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info("Tracing SQL queries for {$target}...");
        }

        try {
            $options = [
                'route' => $route,
                'url' => $url,
                'method' => $method,
            ];

            // Parse JSON parameters if provided
            if ($parameters) {
                $options['parameters'] = json_decode($parameters, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON in parameters option');

                    return self::FAILURE;
                }
            }

            // Parse JSON headers if provided
            if ($headers) {
                $options['headers'] = json_decode($headers, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Invalid JSON in headers option');

                    return self::FAILURE;
                }
            }

            $result = $manager->scan('sql-trace', $options);

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
            $this->error('Error tracing SQL: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data'] ?? [];

        $this->line("Target: <info>{$data['traced_target']}</info>");
        $this->line("Method: <info>{$data['method']}</info>");

        if (isset($data['response_status'])) {
            $this->line("Response Status: <info>{$data['response_status']}</info>");
        }

        if (isset($data['error'])) {
            $this->line("Error: <error>{$data['error']}</error>");
        }

        $this->newLine();
        $this->line('📊 <comment>SQL Statistics:</comment>');
        $this->line("Total Queries: <info>{$data['total_queries']}</info>");
        $this->line("Total Time: <info>{$data['total_time']} ms</info>");

        if (isset($data['statistics'])) {
            $stats = $data['statistics'];
            $this->line('Average Time: <info>'.round($stats['average_time'], 2).' ms</info>');
            $this->line('Slowest Query: <info>'.round($stats['max_time'], 2).' ms</info>');
            $this->line('Fastest Query: <info>'.round($stats['min_time'], 2).' ms</info>');

            if (! empty($stats['query_types'])) {
                $this->line('Query Types: <info>'.implode(', ', array_map(
                    fn ($type, $count): string => "{$type}({$count})",
                    array_keys($stats['query_types']),
                    $stats['query_types']
                )).'</info>');
            }
        }

        // Show slow queries
        if (isset($data['slow_queries']) && ! empty($data['slow_queries'])) {
            $this->newLine();
            $this->line('⚠️  <comment>Slow Queries (>100ms):</comment>');
            foreach ($data['slow_queries'] as $query) {
                $this->line("  <error>{$query['time']} ms</error> - ".mb_substr($query['sql'], 0, 80).'...');
            }
        }

        // Show duplicate queries
        if (isset($data['duplicate_queries']) && ! empty($data['duplicate_queries'])) {
            $this->newLine();
            $this->line('🔄 <comment>Duplicate Queries:</comment>');
            foreach ($data['duplicate_queries'] as $duplicate) {
                $this->line("  <error>{$duplicate['count']} times</error> ({$duplicate['total_time']} ms) - ".mb_substr($duplicate['sql'], 0, 80).'...');
            }
        }

        // Show all queries if not too many
        if (isset($data['queries']) && count($data['queries']) <= 20) {
            $this->newLine();
            $this->line('📝 <comment>All Queries:</comment>');
            foreach ($data['queries'] as $i => $query) {
                $this->line('  '.($i + 1).". <info>{$query['time']} ms</info> - ".$query['sql']);
                if (! empty($query['bindings'])) {
                    $this->line('     Bindings: '.json_encode($query['bindings']));
                }
            }
        } elseif (isset($data['queries']) && count($data['queries']) > 20) {
            $this->newLine();
            $this->line("📝 <comment>Too many queries to display ({$data['total_queries']}). Use --output option to save full results.</comment>");
        }
    }
}
