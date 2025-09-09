<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevSqlDuplicatesCommand extends Command
{
    protected $signature = 'dev:sql:duplicates 
                            {--route= : Specific route to analyze}
                            {--url= : Specific URL to analyze}
                            {--threshold=2 : Duplicate query threshold (default: 2)}
                            {--auto-explain : Run EXPLAIN on detected problematic queries}
                            {--method=GET : HTTP method for the request}
                            {--parameters= : Route parameters as JSON}
                            {--headers= : Request headers as JSON}
                            {--data= : Request data as JSON}
                            {--format=table : Output format (table, json)}
                            {--output= : Output file path}';

    protected $description = 'Analyze SQL queries for N+1 problems, duplicates, and performance issues';

    public function handle(DevtoolboxManager $manager): int
    {
        $route = $this->option('route');
        $url = $this->option('url');
        $format = $this->option('format');
        $output = $this->option('output');

        if (! $route && ! $url) {
            $this->error('Either --route or --url option is required');
            $this->line('Examples:');
            $this->line('  php artisan dev:sql:duplicates --route=users.index');
            $this->line('  php artisan dev:sql:duplicates --url=/api/users');

            return self::FAILURE;
        }

        $options = [
            'route' => $route,
            'url' => $url,
            'threshold' => (int) $this->option('threshold'),
            'auto_explain' => $this->option('auto-explain'),
            'method' => $this->option('method'),
        ];

        // Parse JSON options
        $jsonOptions = ['parameters', 'headers', 'data'];
        foreach ($jsonOptions as $option) {
            $value = $this->option($option);
            if ($value) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("Invalid JSON in --{$option} option");

                    return self::FAILURE;
                }
                $options[$option] = $decoded;
            }
        }

        try {
            if ($format !== 'json') {
                if ($route) {
                    $this->info("🔍 Analyzing SQL queries for route: {$route}");
                } else {
                    $this->info("🔍 Analyzing SQL queries for URL: {$url}");
                }
            }

            $result = $manager->scan('sql-analysis', $options);

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
            $this->error('Error analyzing SQL queries: '.$e->getMessage());

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

        // Display request information
        $this->displayRequestInfo($result['request_info']);

        // Display query analysis
        if (isset($result['query_analysis'])) {
            $this->displayQueryAnalysis($result['query_analysis']);
        }
    }

    private function displayRequestInfo(array $requestInfo): void
    {
        $this->info('📊 Request Information:');
        $this->newLine();

        if ($requestInfo['route']) {
            $this->line("🛣️  Route: {$requestInfo['route']}");
        }
        if ($requestInfo['url']) {
            $this->line("🌐 URL: {$requestInfo['url']}");
        }
        $this->line("📝 Method: {$requestInfo['method']}");

        if ($requestInfo['response_status']) {
            $statusIcon = $requestInfo['response_status'] < 400 ? '✅' : '❌';
            $this->line("{$statusIcon} Response Status: {$requestInfo['response_status']}");
        }

        if ($requestInfo['execution_time']) {
            $this->line("⏱️  Execution Time: {$requestInfo['execution_time']}ms");
        }

        $this->newLine();
    }

    private function displayQueryAnalysis(array $analysis): void
    {
        // Overview statistics
        $this->displayOverviewStats($analysis);

        // Performance issues
        if (! empty($analysis['performance_issues'])) {
            $this->displayPerformanceIssues($analysis['performance_issues']);
        }

        // Duplicate queries
        if (! empty($analysis['duplicate_queries'])) {
            $this->displayDuplicateQueries($analysis['duplicate_queries']);
        }

        // N+1 suspects
        if (! empty($analysis['n_plus_one_suspects'])) {
            $this->displayNPlusOneIssues($analysis['n_plus_one_suspects']);
        }

        // Slow queries
        if (! empty($analysis['slow_queries'])) {
            $this->displaySlowQueries($analysis['slow_queries']);
        }

        // Query breakdown
        $this->displayQueryBreakdown($analysis['query_breakdown']);

        // EXPLAIN results if available
        if (isset($analysis['explain_results']) && ! empty($analysis['explain_results'])) {
            $this->displayExplainResults($analysis['explain_results']);
        }
    }

    private function displayOverviewStats(array $analysis): void
    {
        $this->info('📊 Query Overview:');
        $this->line("   Total Queries: {$analysis['total_queries']}");
        $this->line('   Total Time: '.round($analysis['total_time'], 2).'ms');

        if ($analysis['total_queries'] > 0) {
            $avgTime = round($analysis['total_time'] / $analysis['total_queries'], 2);
            $this->line("   Average Time: {$avgTime}ms");
        }

        $this->newLine();
    }

    private function displayPerformanceIssues(array $issues): void
    {
        $this->warn('⚠️  Performance Issues:');

        foreach ($issues as $issue) {
            $icon = $issue['severity'] === 'error' ? '🚨' : '⚠️';
            $this->line("{$icon} {$issue['message']}");
            $this->line("   💡 {$issue['suggestion']}");
        }

        $this->newLine();
    }

    private function displayDuplicateQueries(array $duplicates): void
    {
        $this->info('🔄 Duplicate Queries:');

        $tableData = [];
        foreach (array_slice($duplicates, 0, 10) as $duplicate) {
            $tableData[] = [
                'Count' => $duplicate['count'],
                'Total Time' => round($duplicate['total_time'], 2).'ms',
                'Avg Time' => round($duplicate['avg_time'], 2).'ms',
                'SQL' => $this->truncate($duplicate['sql'], 60),
            ];
        }

        $this->table(['Count', 'Total Time', 'Avg Time', 'SQL'], $tableData);

        if (count($duplicates) > 10) {
            $remaining = count($duplicates) - 10;
            $this->comment("   ... and {$remaining} more duplicate query patterns");
        }

        $this->newLine();
    }

    private function displayNPlusOneIssues(array $suspects): void
    {
        $this->error('🔁 Potential N+1 Query Issues:');

        foreach ($suspects as $suspect) {
            $this->line('   Pattern: '.$this->truncate($suspect['pattern'], 80));
            $this->line("   Count: {$suspect['count']} queries");
            $this->line('   Total Time: '.round($suspect['total_time'], 2).'ms');
            $this->line("   💡 {$suspect['suggestion']}");
            $this->newLine();
        }
    }

    private function displaySlowQueries(array $slowQueries): void
    {
        $this->warn('🐌 Slow Queries (>100ms):');

        $tableData = [];
        foreach (array_slice($slowQueries, 0, 5) as $query) {
            $tableData[] = [
                'Time' => round($query['time'], 2).'ms',
                'SQL' => $this->truncate($query['sql'], 70),
                'Suggestion' => $this->truncate($query['suggestion'], 40),
            ];
        }

        $this->table(['Time', 'SQL', 'Suggestion'], $tableData);

        if (count($slowQueries) > 5) {
            $remaining = count($slowQueries) - 5;
            $this->comment("   ... and {$remaining} more slow queries");
        }

        $this->newLine();
    }

    private function displayQueryBreakdown(array $breakdown): void
    {
        $this->info('📋 Query Breakdown:');

        // By type
        if (! empty($breakdown['by_type'])) {
            $this->line('   By Type:');
            foreach ($breakdown['by_type'] as $type => $count) {
                $this->line("     • {$type}: {$count}");
            }
        }

        // By table (top 5)
        if (! empty($breakdown['by_table'])) {
            $this->line('   Top Tables:');
            arsort($breakdown['by_table']);
            $topTables = array_slice($breakdown['by_table'], 0, 5, true);
            foreach ($topTables as $table => $count) {
                $this->line("     • {$table}: {$count} queries");
            }
        }

        $this->newLine();
    }

    private function displayExplainResults(array $explains): void
    {
        $this->info('🔍 EXPLAIN Results:');

        foreach ($explains as $explain) {
            $this->comment('Query: '.$this->truncate($explain['query'], 80));

            if (! empty($explain['explain'])) {
                $explainData = [];
                foreach ($explain['explain'] as $row) {
                    $explainRow = [];
                    foreach ($row as $key => $value) {
                        $explainRow[ucfirst($key)] = $value;
                    }
                    $explainData[] = $explainRow;
                }

                if ($explainData !== []) {
                    $headers = array_keys($explainData[0]);
                    $this->table($headers, $explainData);
                }
            }

            $this->newLine();
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
