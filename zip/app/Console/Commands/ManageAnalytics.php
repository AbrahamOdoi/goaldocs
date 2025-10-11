<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ManageAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:manage {action} {--section= : Specific analytics section} {--days=30 : Number of days for trends} {--format=json : Output format (json, csv, table)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage analytics features - cache management, data generation, and reporting';

    /**
     * Execute the console command.
     */
    public function handle(AnalyticsService $analyticsService)
    {
        $action = $this->argument('action');
        $section = $this->option('section');
        $days = $this->option('days');
        $format = $this->option('format');
        
        switch ($action) {
            case 'generate':
                $this->generateAnalytics($analyticsService, $section, $format);
                break;
                
            case 'cache':
                $this->manageCache($analyticsService);
                break;
                
            case 'report':
                $this->generateReport($analyticsService, $format);
                break;
                
            case 'trends':
                $this->showTrends($analyticsService, $days);
                break;
                
            case 'stats':
                $this->showStats($analyticsService, $section);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: generate, cache, report, trends, stats");
                return 1;
        }
        
        return 0;
    }
    
    /**
     * Generate analytics data
     */
    private function generateAnalytics(AnalyticsService $analyticsService, $section = null, $format = 'json')
    {
        $this->info('Generating analytics data...');
        
        $analytics = $analyticsService->getDashboardAnalytics();
        
        if ($section) {
            if (!isset($analytics[$section])) {
                $this->error("Section '{$section}' not found");
                $this->info("Available sections: " . implode(', ', array_keys($analytics)));
                return;
            }
            $data = $analytics[$section];
        } else {
            $data = $analytics;
        }
        
        if ($format === 'json') {
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        } elseif ($format === 'table') {
            $this->displayAsTable($data, $section);
        } else {
            $this->line(json_encode($data));
        }
        
        $this->info('Analytics data generated successfully');
    }
    
    /**
     * Manage analytics cache
     */
    private function manageCache(AnalyticsService $analyticsService)
    {
        $this->info('Managing analytics cache...');
        
        // Clear cache
        $analyticsService->clearCache();
        $this->info('Analytics cache cleared');
        
        // Generate fresh data
        $this->info('Generating fresh analytics data...');
        $analyticsService->getDashboardAnalytics();
        $this->info('Fresh analytics data generated and cached');
        
        // Show cache status
        $cacheKey = 'analytics_dashboard_' . date('Y-m-d');
        $cached = Cache::has($cacheKey);
        $this->info('Cache status: ' . ($cached ? 'Fresh data cached' : 'No cache found'));
    }
    
    /**
     * Generate analytics report
     */
    private function generateReport(AnalyticsService $analyticsService, $format = 'json')
    {
        $this->info('Generating analytics report...');
        
        $analytics = $analyticsService->getDashboardAnalytics();
        
        $report = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => 'CLI Command',
            'overview' => $analytics['overview'],
            'summary' => [
                'total_files' => $analytics['overview']['total_files'],
                'total_users' => $analytics['overview']['total_users'],
                'total_storage_gb' => $analytics['overview']['total_storage_gb'],
                'active_users' => $analytics['overview']['active_users'],
                'files_growth_percent' => $analytics['overview']['files_growth_percent'],
                'users_growth_percent' => $analytics['overview']['users_growth_percent'],
            ],
        ];
        
        if ($format === 'json') {
            $this->line(json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $this->displayReportSummary($report);
        }
        
        $this->info('Analytics report generated successfully');
    }
    
    /**
     * Show trends data
     */
    private function showTrends(AnalyticsService $analyticsService, $days = 30)
    {
        $this->info("Showing trends for the last {$days} days...");
        
        $analytics = $analyticsService->getDashboardAnalytics();
        $trends = $analytics['trends'];
        
        $this->newLine();
        $this->info('Trends Summary:');
        
        foreach ($trends as $metric => $data) {
            $total = collect($data)->sum('count');
            $avg = collect($data)->avg('count');
            $max = collect($data)->max('count');
            
            $this->info("  {$metric}:");
            $this->info("    Total: {$total}");
            $this->info("    Average per day: " . round($avg, 1));
            $this->info("    Peak day: {$max}");
            $this->newLine();
        }
    }
    
    /**
     * Show statistics
     */
    private function showStats(AnalyticsService $analyticsService, $section = null)
    {
        $this->info('Analytics Statistics');
        $this->info('==================');
        
        $analytics = $analyticsService->getDashboardAnalytics();
        
        if ($section) {
            if (!isset($analytics[$section])) {
                $this->error("Section '{$section}' not found");
                $this->info("Available sections: " . implode(', ', array_keys($analytics)));
                return;
            }
            $this->displaySectionStats($analytics[$section], $section);
        } else {
            foreach ($analytics as $sectionName => $sectionData) {
                $this->displaySectionStats($sectionData, $sectionName);
                $this->newLine();
            }
        }
    }
    
    /**
     * Display section statistics
     */
    private function displaySectionStats($data, $sectionName)
    {
        $this->info(ucfirst($sectionName) . ' Statistics:');
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->info("  {$key}: " . count($value) . " items");
                } else {
                    $this->info("  {$key}: {$value}");
                }
            }
        } else {
            $this->info("  Data: " . json_encode($data));
        }
    }
    
    /**
     * Display data as table
     */
    private function displayAsTable($data, $section = null)
    {
        if ($section === 'overview') {
            $headers = ['Metric', 'Value'];
            $rows = [];
            
            foreach ($data as $key => $value) {
                $rows[] = [ucfirst(str_replace('_', ' ', $key)), $value];
            }
            
            $this->table($headers, $rows);
        } elseif ($section === 'file_types') {
            $headers = ['Type', 'Count', 'Percentage'];
            $rows = [];
            
            foreach ($data as $type) {
                $rows[] = [$type['type'], $type['count'], $type['percentage'] . '%'];
            }
            
            $this->table($headers, $rows);
        } else {
            $this->info('Table format not supported for this section');
            $this->line(json_encode($data, JSON_PRETTY_PRINT));
        }
    }
    
    /**
     * Display report summary
     */
    private function displayReportSummary($report)
    {
        $this->info('Analytics Report');
        $this->info('===============');
        $this->info("Generated: {$report['generated_at']}");
        $this->info("Generated by: {$report['generated_by']}");
        $this->newLine();
        
        $this->info('Summary:');
        $this->info("  Total Files: {$report['summary']['total_files']}");
        $this->info("  Total Users: {$report['summary']['total_users']}");
        $this->info("  Total Storage: {$report['summary']['total_storage_gb']} GB");
        $this->info("  Active Users: {$report['summary']['active_users']}");
        $this->info("  Files Growth: {$report['summary']['files_growth_percent']}%");
        $this->info("  Users Growth: {$report['summary']['users_growth_percent']}%");
    }
}
