<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevRoutesWhereCommand extends Command
{
    protected $signature = 'dev:routes:where 
                            {target : Controller class or method to search for (e.g., UserController or UserController@show)}
                            {--show-methods : Show available methods in the target controller}
                            {--include-parameters : Include route parameters in results}
                            {--format=table : Output format (table, json)}
                            {--output= : Output file path}';

    protected $description = 'Find routes that use a specific controller or method (reverse route lookup)';

    public function handle(DevtoolboxManager $manager): int
    {
        $target = $this->argument('target');
        $format = $this->option('format');
        $output = $this->option('output');

        $options = [
            'target' => $target,
            'show_methods' => $this->option('show-methods'),
            'include_parameters' => $this->option('include-parameters'),
        ];

        try {
            // Only show progress message if not outputting JSON directly
            if ($format !== 'json') {
                $this->info("Searching routes for: {$target}...");
            }

            $result = $manager->scan('route-where-lookup', $options);

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
            $this->error('Error searching routes: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        if (isset($result['error'])) {
            $this->error($result['error']);
            if (isset($result['usage'])) {
                $this->line($result['usage']);
            }

            return;
        }

        $this->info("🔍 Route Search Results for: {$result['target']}");
        $this->newLine();

        // Show controller info
        if (isset($result['controller_info'])) {
            $this->displayControllerInfo($result['controller_info']);
        }

        // Show matching routes
        if (isset($result['matching_routes'])) {
            $this->displayMatchingRoutes($result['matching_routes']);
        }

        // Show statistics
        if (isset($result['statistics'])) {
            $this->displayStatistics($result['statistics']);
        }
    }

    private function displayControllerInfo(array $controllerInfo): void
    {
        $this->info('📋 Controller Information:');

        if (! $controllerInfo['exists']) {
            $this->warn("❌ Controller not found: {$controllerInfo['class']}");
            if (isset($controllerInfo['error'])) {
                $this->line("   Error: {$controllerInfo['error']}");
            }
        } else {
            $this->line("✅ Controller: {$controllerInfo['class']}");
            if (isset($controllerInfo['file'])) {
                $this->line("   File: {$controllerInfo['file']}");
            }

            if (isset($controllerInfo['methods'])) {
                $this->line('   Available methods:');
                foreach ($controllerInfo['methods'] as $method) {
                    $params = implode(', ', array_map(function (array $param): string {
                        return ($param['type'] !== 'mixed' ? $param['type'].' ' : '').
                               '$'.$param['name'].
                               ($param['optional'] ? ' = null' : '');
                    }, $method['parameters']));

                    $this->line("     • {$method['name']}({$params})");
                }
            }
        }

        $this->newLine();
    }

    private function displayMatchingRoutes(array $routes): void
    {
        if ($routes === []) {
            $this->warn('❌ No matching routes found.');

            return;
        }

        $this->info('🛣️  Matching Routes ('.count($routes).'):');

        $tableData = [];
        foreach ($routes as $route) {
            $tableData[] = [
                'URI' => $route['uri'],
                'Methods' => implode('|', $route['methods']),
                'Name' => $route['name'] ?: '-',
                'Controller' => $route['controller'],
                'Middleware' => implode(', ', $route['middleware']),
            ];
        }

        $this->table(
            ['URI', 'Methods', 'Name', 'Controller', 'Middleware'],
            $tableData
        );
    }

    private function displayStatistics(array $stats): void
    {
        $this->newLine();
        $this->info('📊 Search Statistics:');
        $this->line("   Total routes: {$stats['total_routes']}");
        $this->line("   Matching routes: {$stats['matching_routes']}");
        $this->line("   Match percentage: {$stats['match_percentage']}%");

        if ($stats['matching_routes'] === 0) {
            $this->newLine();
            $this->comment('💡 Tips:');
            $this->line('   • Make sure the controller name is correct');
            $this->line('   • Try searching for just the class name without namespace');
            $this->line('   • Use --show-methods to see available methods');
        }
    }
}
