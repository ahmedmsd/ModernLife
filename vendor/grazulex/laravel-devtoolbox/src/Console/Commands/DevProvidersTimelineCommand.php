<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\Console\Concerns\HandlesJsonSerialization;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevProvidersTimelineCommand extends Command
{
    use HandlesJsonSerialization;

    protected $signature = 'dev:providers:timeline
                           {--slow-threshold=50 : Threshold in milliseconds to mark providers as slow}
                           {--include-deferred : Include deferred providers in analysis}
                           {--show-dependencies : Show provider dependencies and load order}
                           {--show-bindings : Show services registered by each provider}
                           {--format=table : Output format (table, json)}
                           {--output= : Save output to file}';

    protected $description = 'Analyze service provider boot timeline and performance';

    public function handle(DevtoolboxManager $devtoolbox): int
    {
        $options = [
            'slow_threshold' => (float) $this->option('slow-threshold'),
            'include_deferred' => $this->option('include-deferred'),
            'show_dependencies' => $this->option('show-dependencies'),
            'show_bindings' => $this->option('show-bindings'),
        ];

        try {
            $result = $devtoolbox->scan('provider-timeline', $options);

            $format = $this->option('format');
            $output = $this->formatOutput($result, $format);

            if ($outputFile = $this->option('output')) {
                $this->outputJson($output, $outputFile);

                return 0;
            }

            if ($format === 'json') {
                $this->outputJson($output);
            } else {
                $this->displayResults($result, $options);
            }

            return 0;
        } catch (Exception $e) {
            $this->error("Error analyzing providers: {$e->getMessage()}");

            return 1;
        }
    }

    private function formatOutput(array $result, string $format): array
    {
        if ($format === 'json') {
            return [
                'type' => 'provider-timeline',
                'timestamp' => now()->toISOString(),
                'options' => $result['options'],
                'statistics' => $result['statistics'],
                'providers' => $result['providers'],
                'timeline' => $result['timeline'],
                'slow_providers' => $result['slow_providers'],
                'total_boot_time' => $result['total_boot_time'],
            ];
        }

        return $result;
    }

    private function displayResults(array $result, array $options): void
    {
        $this->displayHeader($result);
        $this->displayStatistics($result['statistics']);

        if (! empty($result['slow_providers'])) {
            $this->displaySlowProviders($result['slow_providers'], $options['slow_threshold']);
        }

        $this->displayProvidersList($result['providers'], $options);

        if ($options['show_dependencies'] || $options['show_bindings']) {
            $this->displayDetailedAnalysis($result['providers'], $options);
        }

        $this->displayTimeline($result['timeline']);
    }

    private function displayHeader(array $result): void
    {
        $this->info('🔍 Analyzing Service Provider Boot Timeline...');
        $this->newLine();

        $this->line('📊 <info>Provider Analysis Summary</info>');
        $this->line("Total Boot Time: <comment>{$result['total_boot_time']}ms</comment>");
        $this->line("Total Providers: <comment>{$result['total_providers']}</comment>");
        $this->newLine();
    }

    private function displayStatistics(array $stats): void
    {
        $this->line('📈 <info>Performance Statistics</info>');
        $this->newLine();

        $headers = ['Metric', 'Value'];
        $rows = [
            ['Total Providers', $stats['total_providers']],
            ['Eager Providers', $stats['eager_providers']],
            ['Deferred Providers', $stats['deferred_providers']],
            ['Slow Providers (>'.$stats['slow_threshold'].'ms)', $stats['slow_providers']],
            ['Average Boot Time', $stats['average_boot_time'].'ms'],
            ['Median Boot Time', $stats['median_boot_time'].'ms'],
            ['Memory Estimate', $this->formatBytes($stats['total_memory_estimate'])],
        ];

        if ($stats['slowest_provider']) {
            $rows[] = ['Slowest Provider', $stats['slowest_provider']['name'].' ('.$stats['slowest_provider']['boot_time'].'ms)'];
        }

        if ($stats['fastest_provider']) {
            $rows[] = ['Fastest Provider', $stats['fastest_provider']['name'].' ('.$stats['fastest_provider']['boot_time'].'ms)'];
        }

        $this->table($headers, $rows);
        $this->newLine();
    }

    private function displaySlowProviders(array $slowProviders, float $threshold): void
    {
        $this->line("🐌 <comment>Slow Providers (>{$threshold}ms)</comment>");
        $this->newLine();

        $headers = ['Provider', 'Boot Time', 'Memory', 'Type', 'File'];
        $rows = [];

        foreach ($slowProviders as $provider) {
            $rows[] = [
                $provider['name'],
                $provider['boot_time'].'ms',
                $this->formatBytes($provider['memory_usage']),
                $provider['is_deferred'] ? 'Deferred' : 'Eager',
                basename($provider['file_path'] ?: 'Unknown'),
            ];
        }

        $this->table($headers, $rows);
        $this->newLine();
    }

    private function displayProvidersList(array $providers, array $options): void
    {
        $this->line('📋 <info>All Providers</info>');
        $this->newLine();

        $headers = ['Provider', 'Boot Time', 'Memory', 'Type'];

        if ($options['show_dependencies']) {
            $headers[] = 'Dependencies';
        }

        if ($options['show_bindings']) {
            $headers[] = 'Bindings';
        }

        $rows = [];

        foreach ($providers as $provider) {
            $row = [
                $provider['name'],
                $provider['boot_time'].'ms',
                $this->formatBytes($provider['memory_usage']),
                $provider['is_deferred'] ? 'Deferred' : 'Eager',
            ];

            if ($options['show_dependencies']) {
                $deps = $provider['dependencies'] ?? [];
                $row[] = count($deps).' deps';
            }

            if ($options['show_bindings']) {
                $bindings = $provider['bindings'] ?? [];
                $row[] = count($bindings).' bindings';
            }

            $rows[] = $row;
        }

        $this->table($headers, $rows);
        $this->newLine();
    }

    private function displayDetailedAnalysis(array $providers, array $options): void
    {
        $this->line('🔍 <info>Detailed Analysis</info>');
        $this->newLine();

        foreach ($providers as $provider) {
            if ($provider['boot_time'] > 10) { // Only show details for providers taking >10ms
                $this->line("📦 <comment>{$provider['name']}</comment>");
                $this->line("   Class: {$provider['class']}");
                $this->line("   Boot Time: {$provider['boot_time']}ms");
                $this->line("   Memory: {$this->formatBytes($provider['memory_usage'])}");
                $this->line('   Type: '.($provider['is_deferred'] ? 'Deferred' : 'Eager'));

                if ($options['show_dependencies'] && ! empty($provider['dependencies'])) {
                    $this->line('   Dependencies:');
                    foreach ($provider['dependencies'] as $dep) {
                        $this->line('     • '.class_basename($dep));
                    }
                }

                if ($options['show_bindings'] && ! empty($provider['bindings'])) {
                    $this->line('   Bindings:');
                    foreach ($provider['bindings'] as $binding) {
                        $this->line("     • {$binding['service']} ({$binding['type']})");
                    }
                }

                $this->newLine();
            }
        }
    }

    private function displayTimeline(array $timeline): void
    {
        $this->line('⏱️ <info>Boot Timeline</info>');
        $this->newLine();

        $headers = ['Order', 'Provider', 'Start (ms)', 'End (ms)', 'Duration (ms)', 'Type'];
        $rows = [];

        foreach ($timeline as $index => $entry) {
            $rows[] = [
                $index + 1,
                $entry['provider'],
                $entry['start_time'],
                $entry['end_time'],
                $entry['duration'],
                $entry['is_deferred'] ? 'Deferred' : 'Eager',
            ];
        }

        // Limit to top 20 for readability
        if (count($rows) > 20) {
            $rows = array_slice($rows, 0, 20);
            $this->table($headers, $rows);
            $this->line('<comment>... and '.(count($timeline) - 20).' more providers</comment>');
        } else {
            $this->table($headers, $rows);
        }

        $this->newLine();
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
