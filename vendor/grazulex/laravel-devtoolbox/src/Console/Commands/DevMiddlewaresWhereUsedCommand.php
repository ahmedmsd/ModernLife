<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\Console\Concerns\HandlesJsonSerialization;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevMiddlewaresWhereUsedCommand extends Command
{
    use HandlesJsonSerialization;

    protected $signature = 'dev:middlewares:where-used 
                            {middleware? : Specific middleware to analyze (class name, alias, or partial name)}
                            {--show-routes : Include detailed route information}
                            {--show-groups : Include route group information}
                            {--show-global : Include global middleware}
                            {--unused-only : Show only unused middleware}
                            {--format=table : Output format (table, json)}
                            {--output= : Output file path}';

    protected $description = 'Analyze middleware usage across routes, controllers, and groups';

    public function handle(DevtoolboxManager $manager): int
    {
        $middleware = $this->argument('middleware');
        $format = $this->option('format');
        $output = $this->option('output');

        $options = [
            'middleware' => $middleware,
            'show_routes' => $this->option('show-routes'),
            'show_groups' => $this->option('show-groups'),
            'show_global' => $this->option('show-global'),
            'unused_only' => $this->option('unused-only'),
        ];

        try {
            if ($format !== 'json') {
                if ($middleware) {
                    $this->info("🔍 Analyzing middleware usage for: {$middleware}");
                } else {
                    $this->info('🔍 Analyzing all middleware usage...');
                }
            }

            $result = $manager->scan('middleware-usage', $options);

            if ($output) {
                $this->outputJson($result, $output);
            } elseif ($format === 'json') {
                $this->outputJson($result);
            } else {
                $this->displayResults($result, $options);
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Error analyzing middleware usage: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result, array $options): void
    {
        if (isset($result['error'])) {
            $this->error($result['error']);

            return;
        }

        // Display statistics first
        $this->displayStatistics($result['statistics']);

        // Display global middleware if requested
        if ($options['show_global'] && isset($result['middleware_analysis']['global_middleware'])) {
            $this->displayGlobalMiddleware($result['middleware_analysis']['global_middleware']);
        }

        // Display middleware usage analysis
        $this->displayMiddlewareAnalysis($result['middleware_analysis'], $options);
    }

    private function displayStatistics(array $stats): void
    {
        $this->info('📊 Middleware Statistics:');
        $this->newLine();

        $this->line("🔧 Total middleware classes: {$stats['total_middleware_classes']}");
        $this->line("🌍 Global middleware: {$stats['global_middleware']}");
        $this->line("🛣️  Route middleware: {$stats['route_middleware']}");
        $this->line("👥 Group middleware: {$stats['group_middleware']}");
        $this->line("✅ Used middleware: {$stats['used_middleware']}");
        $this->line("❌ Unused middleware: {$stats['unused_middleware']}");

        if (! empty($stats['most_used'])) {
            $this->newLine();
            $this->info('🏆 Most Used Middleware:');
            foreach ($stats['most_used'] as $middleware) {
                $this->line("   • {$middleware['name']}: {$middleware['usage_count']} routes");
            }
        }

        if (! empty($stats['never_used'])) {
            $this->newLine();
            $this->warn('🚫 Never Used Middleware:');
            foreach ($stats['never_used'] as $middleware) {
                $this->line("   • {$middleware['name']}");
            }
        }

        $this->newLine();
    }

    private function displayGlobalMiddleware(array $globalMiddleware): void
    {
        if ($globalMiddleware === []) {
            return;
        }

        $this->info('🌍 Global Middleware:');

        $tableData = [];
        foreach ($globalMiddleware as $middleware) {
            $tableData[] = [
                'Name' => $middleware['name'],
                'Class' => $this->truncate($middleware['class'], 50),
                'Applies To' => $middleware['applies_to'],
            ];
        }

        $this->table(['Name', 'Class', 'Applies To'], $tableData);
        $this->newLine();
    }

    private function displayMiddlewareAnalysis(array $analysis, array $options): void
    {
        // Remove global_middleware from analysis if it exists (already displayed)
        unset($analysis['global_middleware']);

        if ($analysis === []) {
            $this->warn('❌ No middleware analysis data found.');

            return;
        }

        $title = $options['unused_only'] ? 'Unused Middleware' : 'Middleware Usage Analysis';
        $this->info("🔍 {$title}:");

        $tableData = [];
        foreach ($analysis as $data) {
            $middleware = $data['middleware'];

            $row = [
                'Name' => $middleware['name'],
                'Type' => ucfirst($middleware['type']),
                'Usage Count' => $data['usage_count'],
                'Global' => $data['used_as_global'] ? '✅ Yes' : '❌ No',
            ];

            if (isset($middleware['alias'])) {
                $row['Alias'] = $middleware['alias'];
            }

            $tableData[] = $row;
        }

        $headers = ['Name', 'Type', 'Usage Count', 'Global'];
        if (isset($tableData[0]['Alias'])) {
            $headers[] = 'Alias';
        }

        $this->table($headers, $tableData);

        // Show detailed route information if requested
        if ($options['show_routes']) {
            $this->displayDetailedRouteUsage($analysis);
        }

        // Show group information if requested
        if ($options['show_groups']) {
            $this->displayGroupUsage($analysis);
        }
    }

    private function displayDetailedRouteUsage(array $analysis): void
    {
        foreach ($analysis as $data) {
            if (empty($data['routes'])) {
                continue;
            }

            $middleware = $data['middleware'];
            $this->newLine();
            $this->comment("▶ Routes using {$middleware['name']} (".count($data['routes']).' routes):');

            $routeTableData = [];
            foreach (array_slice($data['routes'], 0, 10) as $route) { // Limit to 10 routes
                $routeTableData[] = [
                    'URI' => $route['uri'],
                    'Methods' => implode('|', $route['methods']),
                    'Name' => $route['name'] ?: '-',
                    'Action' => $this->truncate($route['action'], 40),
                ];
            }

            $this->table(
                ['URI', 'Methods', 'Name', 'Action'],
                $routeTableData
            );

            if (count($data['routes']) > 10) {
                $remaining = count($data['routes']) - 10;
                $this->comment("   ... and {$remaining} more routes");
            }
        }
    }

    private function displayGroupUsage(array $analysis): void
    {
        foreach ($analysis as $data) {
            if (empty($data['groups_used'])) {
                continue;
            }

            $middleware = $data['middleware'];
            $this->newLine();
            $this->comment("▶ Group usage for {$middleware['name']}:");

            foreach ($data['groups_used'] as $groupInfo) {
                $this->line("   • Applied as: {$groupInfo['middleware_applied']}");
                $this->line("     Route pattern: {$groupInfo['route_pattern']}");
            }
        }
    }

    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3).'...';
    }
}
