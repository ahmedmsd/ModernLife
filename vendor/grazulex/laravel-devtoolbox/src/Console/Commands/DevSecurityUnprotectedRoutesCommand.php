<?php

declare(strict_types=1);

namespace Grazulex\LaravelDevtoolbox\Console\Commands;

use Exception;
use Grazulex\LaravelDevtoolbox\DevtoolboxManager;
use Illuminate\Console\Command;

final class DevSecurityUnprotectedRoutesCommand extends Command
{
    protected $signature = 'dev:security:unprotected-routes 
                            {--critical-only : Show only critical security issues}
                            {--exclude=* : Route patterns to exclude from check}
                            {--format=table : Output format (table, json)}
                            {--output= : Output file path}';

    protected $description = 'Scan for routes that are not protected by authentication middleware';

    public function handle(DevtoolboxManager $manager): int
    {
        $criticalOnly = $this->option('critical-only');
        $exclude = $this->option('exclude');
        $format = $this->option('format');
        $output = $this->option('output');

        // Only show progress message if not outputting JSON directly
        if ($format !== 'json') {
            $this->info('🔐 Scanning for unprotected routes...');
        }

        try {
            $result = $manager->scan('security', [
                'check_unprotected_routes' => true,
                'check_csrf_protection' => true,
                'critical_only' => $criticalOnly,
                'exclude_patterns' => $exclude,
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
            $this->error('Error scanning security: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function displayResults(array $result): void
    {
        $data = $result['data'] ?? [];
        $unprotectedRoutes = $data['unprotected_routes'] ?? [];
        $csrfVulnerableRoutes = $data['csrf_vulnerable_routes'] ?? [];
        $securityScore = $data['security_score'] ?? 0;

        // Security Score
        $this->displaySecurityScore($securityScore);
        $this->newLine();

        // Unprotected Routes
        if (! empty($unprotectedRoutes)) {
            $this->error('🚨 Unprotected Routes Found:');
            $this->displayUnprotectedRoutes($unprotectedRoutes);
        } else {
            $this->info('✅ No unprotected routes found!');
        }

        $this->newLine();

        // CSRF Vulnerable Routes
        if (! empty($csrfVulnerableRoutes)) {
            $this->error('🛡️ CSRF Vulnerable Routes Found:');
            $this->displayCsrfVulnerableRoutes($csrfVulnerableRoutes);
        } else {
            $this->info('✅ No CSRF vulnerable routes found!');
        }

        $this->newLine();
        $this->displaySummary($data);
    }

    private function displaySecurityScore(int $score): void
    {
        $color = 'red';
        $emoji = '🔴';

        if ($score >= 80) {
            $color = 'green';
            $emoji = '🟢';
        } elseif ($score >= 60) {
            $color = 'yellow';
            $emoji = '🟡';
        }

        $this->line("<fg={$color}>{$emoji} Security Score: {$score}/100</>");
    }

    private function displayUnprotectedRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $severity = $route['severity'];
            $severityColor = $this->getSeverityColor($severity);
            $severityEmoji = $this->getSeverityEmoji($severity);

            $methods = implode('|', $route['methods']);
            $this->line("<fg={$severityColor}>{$severityEmoji} [{$severity}] {$methods} {$route['uri']}</>");

            if (! empty($route['name'])) {
                $this->line("   📛 Name: {$route['name']}");
            }

            if (! empty($route['action'])) {
                $this->line("   🎯 Action: {$route['action']}");
            }

            if (! empty($route['middleware'])) {
                $middleware = implode(', ', $route['middleware']);
                $this->line("   🛡️ Current Middleware: {$middleware}");
            }

            $this->line("   💡 Recommendation: {$route['recommendation']}");
            $this->newLine();
        }
    }

    private function displayCsrfVulnerableRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $methods = implode('|', $route['methods']);
            $this->line("<fg=red>🛡️ [CSRF] {$methods} {$route['uri']}</>");

            if (! empty($route['name'])) {
                $this->line("   📛 Name: {$route['name']}");
            }

            $this->line("   💡 Recommendation: {$route['recommendation']}");
            $this->newLine();
        }
    }

    private function displaySummary(array $data): void
    {
        $totalRoutes = $data['total_routes'] ?? 0;
        $unprotectedCount = count($data['unprotected_routes'] ?? []);
        $csrfVulnerableCount = count($data['csrf_vulnerable_routes'] ?? []);

        $this->info('📊 Security Summary:');
        $this->line("   • Total Routes: {$totalRoutes}");
        $this->line("   • Unprotected Routes: {$unprotectedCount}");
        $this->line("   • CSRF Vulnerable Routes: {$csrfVulnerableCount}");

        if ($unprotectedCount > 0 || $csrfVulnerableCount > 0) {
            $this->newLine();
            $this->warn('🔧 Quick Fixes:');
            $this->line('   • Add "auth" middleware to routes requiring authentication');
            $this->line('   • Use "web" middleware group for web routes to enable CSRF protection');
            $this->line('   • Consider using "auth:sanctum" for API routes');
        }
    }

    private function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'critical' => 'red',
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'blue',
            default => 'white',
        };
    }

    private function getSeverityEmoji(string $severity): string
    {
        return match ($severity) {
            'critical' => '🚨',
            'high' => '⚠️',
            'medium' => '⚡',
            'low' => 'ℹ️',
            default => '•',
        };
    }
}
