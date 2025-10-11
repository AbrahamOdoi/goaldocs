<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportGeneration;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// Helper function for formatting bytes
if (!function_exists('formatBytes')) {
    function formatBytes($bytes) {
        if ($bytes === 0) return '0 B';
        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}

class ReportService
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Create a new report.
     */
    public function createReport(array $data, User $user): Report
    {
        $report = Report::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'config' => $data['config'] ?? [],
            'schedule' => $data['schedule'] ?? null,
            'is_public' => $data['is_public'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ]);

        // Set next generation date if scheduled
        if ($report->isScheduled()) {
            $report->update([
                'next_generation_at' => $report->getNextGenerationDate()
            ]);
        }

        return $report;
    }

    /**
     * Update an existing report.
     */
    public function updateReport(Report $report, array $data): Report
    {
        $report->update($data);

        // Update next generation date if schedule changed
        if (isset($data['schedule']) && $report->isScheduled()) {
            $report->update([
                'next_generation_at' => $report->getNextGenerationDate()
            ]);
        }

        return $report;
    }

    /**
     * Generate a report.
     */
    public function generateReport(Report $report, User $user, string $format = 'pdf'): ReportGeneration
    {
        // Create generation record
        $generation = ReportGeneration::create([
            'report_id' => $report->id,
            'user_id' => $user->id,
            'status' => ReportGeneration::STATUS_PROCESSING,
            'format' => $format,
            'generated_at' => now(),
        ]);

        try {
            // Get analytics data based on report type
            $analyticsData = $this->getAnalyticsData($report);

            // Generate file based on format
            $filePath = $this->generateFile($report, $analyticsData, $format);

            // Update generation record
            $generation->update([
                'status' => ReportGeneration::STATUS_COMPLETED,
                'file_path' => $filePath,
                'file_size' => Storage::disk('local')->size($filePath),
                'generated_at' => now(),
            ]);

            // Update report last generated timestamp
            $report->update([
                'last_generated_at' => now(),
                'next_generation_at' => $report->getNextGenerationDate()
            ]);

        } catch (\Exception $e) {
            Log::error('Report generation failed', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $generation->update([
                'status' => ReportGeneration::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }

        return $generation;
    }

    /**
     * Get analytics data for report type.
     */
    protected function getAnalyticsData(Report $report): array
    {
        $period = $report->getConfig('period', '30d');
        $userId = $report->getConfig('user_id');

        switch ($report->type) {
            case Report::TYPE_DOCUMENT_USAGE:
                return $this->analyticsService->getDocumentUsageAnalytics($userId, $period);
            
            case Report::TYPE_PROCESSING:
                return $this->analyticsService->getProcessingAnalytics($userId, $period);
            
            case Report::TYPE_STORAGE:
                return $this->analyticsService->getStorageAnalytics($userId, $period);
            
            case Report::TYPE_USER_ACTIVITY:
                return $this->analyticsService->getUserActivityAnalytics($userId, $period);
            
            default:
                return [];
        }
    }

    /**
     * Generate file based on format.
     */
    protected function generateFile(Report $report, array $data, string $format): string
    {
        $fileName = 'reports/' . $report->id . '_' . time() . '.' . $format;
        
        switch ($format) {
            case 'pdf':
                return $this->generatePdf($report, $data, $fileName);
            
            case 'excel':
                return $this->generateExcel($report, $data, $fileName);
            
            case 'csv':
                return $this->generateCsv($report, $data, $fileName);
            
            default:
                throw new \InvalidArgumentException("Unsupported format: {$format}");
        }
    }

    /**
     * Generate PDF report.
     */
    protected function generatePdf(Report $report, array $data, string $fileName): string
    {
        $html = view('reports.pdf.' . $report->type, [
            'report' => $report,
            'data' => $data,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('a4', 'portrait');
        
        Storage::disk('local')->put($fileName, $pdf->output());
        
        return $fileName;
    }

    /**
     * Generate Excel report.
     */
    protected function generateExcel(Report $report, array $data, string $fileName): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', $report->name);
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);

        // Set subtitle
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Add data based on report type
        $this->addExcelData($sheet, $report, $data);

        $writer = new Xlsx($spreadsheet);
        Storage::disk('local')->put($fileName, '');
        $writer->save(Storage::disk('local')->path($fileName));
        
        return $fileName;
    }

    /**
     * Generate CSV report.
     */
    protected function generateCsv(Report $report, array $data, string $fileName): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add data based on report type
        $this->addExcelData($sheet, $report, $data);

        $writer = new Csv($sheet);
        Storage::disk('local')->put($fileName, '');
        $writer->save(Storage::disk('local')->path($fileName));
        
        return $fileName;
    }

    /**
     * Add data to Excel/CSV sheet.
     */
    protected function addExcelData($sheet, Report $report, array $data): void
    {
        $row = 4; // Start after title and subtitle

        switch ($report->type) {
            case Report::TYPE_DOCUMENT_USAGE:
                $this->addDocumentUsageData($sheet, $data, $row);
                break;
            
            case Report::TYPE_PROCESSING:
                $this->addProcessingData($sheet, $data, $row);
                break;
            
            case Report::TYPE_STORAGE:
                $this->addStorageData($sheet, $data, $row);
                break;
            
            case Report::TYPE_USER_ACTIVITY:
                $this->addUserActivityData($sheet, $data, $row);
                break;
        }
    }

    /**
     * Add document usage data to sheet.
     */
    protected function addDocumentUsageData($sheet, array $data, int &$row): void
    {
        // Summary
        $sheet->setCellValue("A{$row}", 'Summary');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Activities');
        $sheet->setCellValue("B{$row}", $data['total_activities'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'File Views');
        $sheet->setCellValue("B{$row}", $data['file_views'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'File Downloads');
        $sheet->setCellValue("B{$row}", $data['file_downloads'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'File Uploads');
        $sheet->setCellValue("B{$row}", $data['file_uploads'] ?? 0);
        $row++;

        // Top Files
        $row++;
        $sheet->setCellValue("A{$row}", 'Top Files');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'File Name');
        $sheet->setCellValue("B{$row}", 'Activity Count');
        $sheet->setCellValue("C{$row}", 'Size');
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['top_files'] ?? [] as $file) {
            $sheet->setCellValue("A{$row}", $file['file_name'] ?? '');
            $sheet->setCellValue("B{$row}", $file['activity_count'] ?? 0);
            $sheet->setCellValue("C{$row}", $file['formatted_size'] ?? '');
            $row++;
        }
    }

    /**
     * Add processing data to sheet.
     */
    protected function addProcessingData($sheet, array $data, int &$row): void
    {
        // OCR Summary
        $sheet->setCellValue("A{$row}", 'OCR Processing');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Processed');
        $sheet->setCellValue("B{$row}", $data['ocr']['total_processed'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Successful');
        $sheet->setCellValue("B{$row}", $data['ocr']['successful'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Failed');
        $sheet->setCellValue("B{$row}", $data['ocr']['failed'] ?? 0);
        $row++;

        // Conversion Summary
        $row++;
        $sheet->setCellValue("A{$row}", 'Document Conversion');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Conversions');
        $sheet->setCellValue("B{$row}", $data['conversion']['total_conversions'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Successful');
        $sheet->setCellValue("B{$row}", $data['conversion']['successful'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Failed');
        $sheet->setCellValue("B{$row}", $data['conversion']['failed'] ?? 0);
        $row++;
    }

    /**
     * Add storage data to sheet.
     */
    protected function addStorageData($sheet, array $data, int &$row): void
    {
        // Storage Summary
        $sheet->setCellValue("A{$row}", 'Storage Summary');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Storage Used');
        $sheet->setCellValue("B{$row}", $data['formatted_storage'] ?? '0 B');
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Files');
        $sheet->setCellValue("B{$row}", $data['total_files'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Average File Size');
        $sheet->setCellValue("B{$row}", $data['average_file_size'] ? formatBytes($data['average_file_size']) : '0 B');
        $row++;

        // File Type Distribution
        $row++;
        $sheet->setCellValue("A{$row}", 'File Type Distribution');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'File Type');
        $sheet->setCellValue("B{$row}", 'Count');
        $sheet->setCellValue("C{$row}", 'Total Size');
        $sheet->getStyle("A{$row}:C{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['file_type_distribution'] ?? [] as $extension => $fileData) {
            $sheet->setCellValue("A{$row}", strtoupper($extension));
            $sheet->setCellValue("B{$row}", $fileData['count'] ?? 0);
            $sheet->setCellValue("C{$row}", formatBytes($fileData['total_size'] ?? 0));
            $row++;
        }
    }

    /**
     * Add user activity data to sheet.
     */
    protected function addUserActivityData($sheet, array $data, int &$row): void
    {
        // Activity Summary
        $sheet->setCellValue("A{$row}", 'Activity Summary');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Activities');
        $sheet->setCellValue("B{$row}", $data['total_activities'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Active Users');
        $sheet->setCellValue("B{$row}", $data['active_users'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Activity Types');
        $sheet->setCellValue("B{$row}", count($data['activity_by_type'] ?? []));
        $row++;

        // Activity by Type
        $row++;
        $sheet->setCellValue("A{$row}", 'Activity by Type');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A{$row}", 'Activity Type');
        $sheet->setCellValue("B{$row}", 'Count');
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $row++;

        foreach ($data['activity_by_type'] ?? [] as $type => $count) {
            $sheet->setCellValue("A{$row}", ucwords(str_replace('_', ' ', $type)));
            $sheet->setCellValue("B{$row}", $count);
            $row++;
        }
    }

    /**
     * Get reports for user.
     */
    public function getUserReports(User $user, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Report::where('user_id', $user->id);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get public reports.
     */
    public function getPublicReports(): \Illuminate\Database\Eloquent\Collection
    {
        return Report::public()->active()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get scheduled reports that need generation.
     */
    public function getScheduledReports(): \Illuminate\Database\Eloquent\Collection
    {
        return Report::scheduled()
            ->active()
            ->where('next_generation_at', '<=', now())
            ->get();
    }

    /**
     * Delete a report and its generations.
     */
    public function deleteReport(Report $report): bool
    {
        // Delete generated files
        foreach ($report->generations as $generation) {
            if ($generation->file_path) {
                Storage::disk('local')->delete($generation->file_path);
            }
        }

        return $report->delete();
    }
}
