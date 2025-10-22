<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\Console\Concerns\HandlesJsonSerialization;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevContainerBindingsCommand extends Command
{
    use HandlesJsonSerialization;

    protected $signature = 'dev:container:bindings 
                            {--filter= : Filter bindings by name, namespace, or type}
                            {--show-resolved : Attempt to resolve bindings and show actual instances}
                            {--show-parameters : Show constructor parameters for classes}
                            {--show-aliases : Include container aliases in output}
                            {--group-by=type : Group results by (type, namespace, singleton)}
                            {--format=table : Output format (table, json)}
                            {--output= : Output file path}';

    protected $description = 'Analyze Laravel container bindings, singletons, and dependency injection mappings';

    public function handle(DevtoolboxManager $manager): int
    {
        $format = $this->option('format');
        $output = $this->option('output');

        $options = [
            'filter' => $this->option('filter'),
            'show_resolved' => $this->option('show-resolved'),
            'show_parameters' => $this->option('show-parameters'),
            'show_aliases' => $this->option('show-aliases'),
            'group_by' => $this->option('group-by'),
        ];

        try {
            if ($format !== 'json') {
                $this->info('🔍 Analyzing container bindings...');
            }

            $result = $manager->scan('container-bindings', $options);

            if ($output) {
                $this->outputJson($result, $output);
            } elseif ($format === 'json') {
                $this->outputJson($result);
            } else {
                $this->displayResults($result, $options);
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('Error analyzing container bindings: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result, array $options): void
    {
        if (isset($result['error'])) {
            $this->error($result['error']);

            return;
        }

        // Display statistics
        $this->displayStatistics($result['statistics']);

        // Display aliases if requested
        if ($options['show_aliases'] && ! empty($result['aliases'])) {
            $this->displayAliases($result['aliases']);
        }

        // Display grouped bindings
        $this->displayBindings($result['grouped'], $options);
    }

    private function displayStatistics(array $stats): void
    {
        $this->info('📊 Container Statistics:');
        $this->newLine();

        $this->line("📦 Total bindings: {$stats['total_bindings']}");
        $this->line("🔗 Total aliases: {$stats['total_aliases']}");
        $this->line("🏠 Singletons: {$stats['singletons']}");
        $this->line("🔌 Interfaces: {$stats['interfaces']}");
        $this->line("📋 Classes: {$stats['classes']}");
        $this->line("⚡ Closures: {$stats['closures']}");
        $this->line("📁 Unique namespaces: {$stats['unique_namespaces']}");

        if (! empty($stats['namespaces'])) {
            $this->newLine();
            $this->info('🏷️  Top Namespaces:');
            arsort($stats['namespaces']);
            $top = array_slice($stats['namespaces'], 0, 5, true);
            foreach ($top as $namespace => $count) {
                $this->line("   {$namespace}: {$count} bindings");
            }
        }

        $this->newLine();
    }

    private function displayAliases(array $aliases): void
    {
        if ($aliases === []) {
            return;
        }

        $this->info('🔗 Container Aliases:');

        $tableData = [];
        foreach ($aliases as $alias => $abstract) {
            $tableData[] = [
                'Alias' => $alias,
                'Target' => $abstract,
            ];
        }

        $this->table(['Alias', 'Target'], $tableData);
        $this->newLine();
    }

    private function displayBindings(array $grouped, array $options): void
    {
        $this->info("📋 Container Bindings (grouped by {$options['group_by']}):");

        foreach ($grouped as $group => $bindings) {
            $this->newLine();
            $this->comment("▶ {$group} (".count($bindings).' bindings)');

            $tableData = [];
            foreach ($bindings as $binding) {
                $row = [
                    'Abstract' => $this->truncate($binding['abstract'], 40),
                    'Concrete' => $this->truncate($binding['concrete'], 30),
                    'Type' => $this->getTypeIcon($binding),
                    'Scope' => $binding['is_singleton'] ? '🏠 Singleton' : '🔄 Transient',
                ];

                // Add resolved class if available
                if (isset($binding['resolved_class'])) {
                    $row['Resolved'] = $this->truncate($binding['resolved_class'], 25);
                } elseif (isset($binding['can_resolve']) && ! $binding['can_resolve']) {
                    $row['Resolved'] = '❌ Failed';
                }

                // Add constructor parameters count if available
                if ($options['show_parameters'] && isset($binding['constructor_parameters'])) {
                    $paramCount = count($binding['constructor_parameters']);
                    $row['Params'] = $paramCount > 0 ? "📝 {$paramCount}" : '∅';
                }

                $tableData[] = $row;
            }

            // Define headers based on what we're showing
            $headers = ['Abstract', 'Concrete', 'Type', 'Scope'];
            if (isset($tableData[0]['Resolved'])) {
                $headers[] = 'Resolved';
            }
            if ($options['show_parameters']) {
                $headers[] = 'Params';
            }

            $this->table($headers, array_slice($tableData, 0, 20)); // Limit to 20 per group

            if (count($bindings) > 20) {
                $remaining = count($bindings) - 20;
                $this->comment("   ... and {$remaining} more bindings");
            }

            // Show detailed parameter info for first few if requested
            if ($options['show_parameters'] && count($bindings) > 0) {
                $this->displayDetailedParameters(array_slice($bindings, 0, 3));
            }
        }
    }

    private function displayDetailedParameters(array $bindings): void
    {
        foreach ($bindings as $binding) {
            if (! isset($binding['constructor_parameters'])) {
                continue;
            }
            if (empty($binding['constructor_parameters'])) {
                continue;
            }
            $this->newLine();
            $this->line("🔧 Constructor parameters for: {$binding['abstract']}");

            foreach ($binding['constructor_parameters'] as $param) {
                $type = $param['type'] !== 'mixed' ? $param['type'].' ' : '';
                $optional = $param['optional'] ? ' (optional)' : ' (required)';
                $default = $param['has_default'] ? ' = '.var_export($param['default_value'], true) : '';

                $this->line("   • {$type}\${$param['name']}{$default}{$optional}");
            }
        }
    }

    private function getTypeIcon(array $binding): string
    {
        if ($binding['is_interface']) {
            return '🔌 Interface';
        }

        if ($binding['concrete'] === 'Closure') {
            return '⚡ Closure';
        }

        if ($binding['is_class']) {
            return '📋 Class';
        }

        return '❓ Other';
    }

    private function truncate(string $text, int $length): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length - 3).'...';
    }
}
