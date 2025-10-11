<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:generate-scheduled {--force : Force generation of all scheduled reports}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate scheduled reports that are due';

    /**
     * The report service instance.
     *
     * @var ReportService
     */
    protected $reportService;

    /**
     * Create a new command instance.
     *
     * @param ReportService $reportService
     * @return void
     */
    public function __construct(ReportService $reportService)
    {
        parent::__construct();
        $this->reportService = $reportService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting scheduled report generation...');

        $force = $this->option('force');
        
        if ($force) {
            $reports = Report::scheduled()->active()->get();
            $this->info("Force mode: Processing {$reports->count()} scheduled reports");
        } else {
            $reports = $this->reportService->getScheduledReports();
            $this->info("Found {$reports->count()} reports due for generation");
        }

        if ($reports->isEmpty()) {
            $this->info('No reports to generate.');
            return 0;
        }

        $bar = $this->output->createProgressBar($reports->count());
        $bar->start();

        $successCount = 0;
        $errorCount = 0;

        foreach ($reports as $report) {
            try {
                $this->line("\nProcessing report: {$report->name}");

                // Generate report in PDF format by default
                $generation = $this->reportService->generateReport($report, $report->user, 'pdf');

                if ($generation->isCompleted()) {
                    $this->info("✓ Generated successfully: {$generation->file_path}");
                    $successCount++;
                } else {
                    $this->error("✗ Generation failed: " . ($generation->error_message ?? 'Unknown error'));
                    $errorCount++;
                }

            } catch (\Exception $e) {
                $this->error("✗ Error processing report {$report->name}: " . $e->getMessage());
                Log::error('Scheduled report generation failed', [
                    'report_id' => $report->id,
                    'report_name' => $report->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $errorCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Generation completed:");
        $this->info("  ✓ Successful: {$successCount}");
        $this->info("  ✗ Failed: {$errorCount}");

        return $errorCount === 0 ? 0 : 1;
    }
}
