<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SecurityLog;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ComplianceService
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Generate compliance report for a specific standard.
     */
    public function generateComplianceReport($standard, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate) : now()->subDays(30);
        $endDate = $endDate ? Carbon::parse($endDate) : now();

        $cacheKey = "compliance_report_{$standard}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        return Cache::remember($cacheKey, 3600, function () use ($standard, $startDate, $endDate) {
            return match($standard) {
                AuditLog::COMPLIANCE_GDPR => $this->generateGDPRReport($startDate, $endDate),
                AuditLog::COMPLIANCE_HIPAA => $this->generateHIPAAReport($startDate, $endDate),
                AuditLog::COMPLIANCE_SOX => $this->generateSOXReport($startDate, $endDate),
                AuditLog::COMPLIANCE_PCI => $this->generatePCIReport($startDate, $endDate),
                AuditLog::COMPLIANCE_ISO27001 => $this->generateISO27001Report($startDate, $endDate),
                default => $this->generateGeneralComplianceReport($startDate, $endDate),
            };
        });
    }

    /**
     * Generate GDPR compliance report.
     */
    private function generateGDPRReport($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->byComplianceStandard(AuditLog::COMPLIANCE_GDPR)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'standard' => AuditLog::COMPLIANCE_GDPR,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'user_data_access' => $auditLogs->where('resource_type', AuditLog::RESOURCE_USER)->count(),
                'data_exports' => $auditLogs->where('action', AuditLog::ACTION_EXPORT)->count(),
                'data_deletions' => $auditLogs->where('action', AuditLog::ACTION_DELETE)->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs),
            'violations' => $this->identifyViolations($auditLogs, 'GDPR'),
            'recommendations' => $this->generateRecommendations($auditLogs, 'GDPR'),
        ];
    }

    /**
     * Generate HIPAA compliance report.
     */
    private function generateHIPAAReport($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->byComplianceStandard(AuditLog::COMPLIANCE_HIPAA)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'standard' => AuditLog::COMPLIANCE_HIPAA,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'phi_access' => $auditLogs->where('resource_type', AuditLog::RESOURCE_FILE)->count(),
                'unauthorized_access' => $auditLogs->where('metadata->unauthorized', true)->count(),
                'data_breaches' => $auditLogs->where('metadata->breach_detected', true)->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs),
            'violations' => $this->identifyViolations($auditLogs, 'HIPAA'),
            'recommendations' => $this->generateRecommendations($auditLogs, 'HIPAA'),
        ];
    }

    /**
     * Generate SOX compliance report.
     */
    private function generateSOXReport($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->byComplianceStandard(AuditLog::COMPLIANCE_SOX)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'standard' => AuditLog::COMPLIANCE_SOX,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'financial_data_access' => $auditLogs->where('resource_type', AuditLog::RESOURCE_DOCUMENT)->count(),
                'data_modifications' => $auditLogs->where('action', AuditLog::ACTION_UPDATE)->count(),
                'data_deletions' => $auditLogs->where('action', AuditLog::ACTION_DELETE)->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs),
            'violations' => $this->identifyViolations($auditLogs, 'SOX'),
            'recommendations' => $this->generateRecommendations($auditLogs, 'SOX'),
        ];
    }

    /**
     * Generate PCI compliance report.
     */
    private function generatePCIReport($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->byComplianceStandard(AuditLog::COMPLIANCE_PCI)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'standard' => AuditLog::COMPLIANCE_PCI,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'card_data_access' => $auditLogs->where('resource_type', AuditLog::RESOURCE_FILE)->count(),
                'unauthorized_access' => $auditLogs->where('metadata->unauthorized', true)->count(),
                'data_breaches' => $auditLogs->where('metadata->breach_detected', true)->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs),
            'violations' => $this->identifyViolations($auditLogs, 'PCI'),
            'recommendations' => $this->generateRecommendations($auditLogs, 'PCI'),
        ];
    }

    /**
     * Generate ISO27001 compliance report.
     */
    private function generateISO27001Report($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->byComplianceStandard(AuditLog::COMPLIANCE_ISO27001)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $securityIncidents = SecurityLog::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('severity', [SecurityLog::SEVERITY_HIGH, SecurityLog::SEVERITY_CRITICAL])
            ->count();

        return [
            'standard' => AuditLog::COMPLIANCE_ISO27001,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'security_incidents' => $securityIncidents,
                'data_access' => $auditLogs->where('action', AuditLog::ACTION_READ)->count(),
                'data_modifications' => $auditLogs->where('action', AuditLog::ACTION_UPDATE)->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs, $securityIncidents),
            'violations' => $this->identifyViolations($auditLogs, 'ISO27001', $securityIncidents),
            'recommendations' => $this->generateRecommendations($auditLogs, 'ISO27001', $securityIncidents),
        ];
    }

    /**
     * Generate general compliance report.
     */
    private function generateGeneralComplianceReport($startDate, $endDate): array
    {
        $auditLogs = AuditLog::complianceRelated()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return [
            'standard' => 'General',
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_events' => $auditLogs->count(),
                'compliance_events' => $auditLogs->count(),
                'standards_covered' => $auditLogs->pluck('compliance_standard')->unique()->count(),
            ],
            'compliance_score' => $this->calculateComplianceScore($auditLogs),
            'violations' => [],
            'recommendations' => $this->generateRecommendations($auditLogs, 'General'),
        ];
    }

    /**
     * Calculate compliance score.
     */
    private function calculateComplianceScore($auditLogs, $securityIncidents = 0): float
    {
        $totalEvents = $auditLogs->count() + $securityIncidents;
        if ($totalEvents === 0) return 100.0;

        $violations = $this->countViolations($auditLogs, $securityIncidents);
        
        return max(0, 100 - ($violations / $totalEvents) * 100);
    }

    /**
     * Count violations.
     */
    private function countViolations($auditLogs, $securityIncidents = 0): int
    {
        $violations = 0;

        // Count unauthorized access
        $violations += $auditLogs->where('metadata->unauthorized', true)->count();
        
        // Count data breaches
        $violations += $auditLogs->where('metadata->breach_detected', true)->count();
        
        // Count security incidents
        $violations += $securityIncidents;

        return $violations;
    }

    /**
     * Identify violations.
     */
    private function identifyViolations($auditLogs, $standard, $securityIncidents = 0): array
    {
        $violations = [];

        // Check for unauthorized access
        $unauthorizedAccess = $auditLogs->where('metadata->unauthorized', true);
        if ($unauthorizedAccess->count() > 0) {
            $violations[] = [
                'type' => 'Unauthorized Access',
                'count' => $unauthorizedAccess->count(),
                'severity' => 'High',
                'description' => "Unauthorized access detected for {$standard} compliance",
            ];
        }

        // Check for data breaches
        $dataBreaches = $auditLogs->where('metadata->breach_detected', true);
        if ($dataBreaches->count() > 0) {
            $violations[] = [
                'type' => 'Data Breach',
                'count' => $dataBreaches->count(),
                'severity' => 'Critical',
                'description' => "Data breach detected for {$standard} compliance",
            ];
        }

        // Check for security incidents (ISO27001)
        if ($standard === 'ISO27001' && $securityIncidents > 0) {
            $violations[] = [
                'type' => 'Security Incident',
                'count' => $securityIncidents,
                'severity' => 'High',
                'description' => 'Security incidents detected for ISO27001 compliance',
            ];
        }

        return $violations;
    }

    /**
     * Generate recommendations.
     */
    private function generateRecommendations($auditLogs, $standard, $securityIncidents = 0): array
    {
        $recommendations = [];

        $totalEvents = $auditLogs->count();
        
        if ($totalEvents > 100) {
            $recommendations[] = [
                'priority' => 'Medium',
                'title' => 'Review Access Patterns',
                'description' => "High number of {$standard} compliance events. Review access patterns and implement additional controls.",
            ];
        }

        $unauthorizedAccess = $auditLogs->where('metadata->unauthorized', true)->count();
        if ($unauthorizedAccess > 0) {
            $recommendations[] = [
                'priority' => 'High',
                'title' => 'Investigate Unauthorized Access',
                'description' => "Unauthorized access attempts detected for {$standard} compliance. Immediate investigation required.",
            ];
        }

        if ($standard === 'ISO27001' && $securityIncidents > 5) {
            $recommendations[] = [
                'priority' => 'High',
                'title' => 'Review Security Controls',
                'description' => 'High number of security incidents. Review and strengthen security controls.',
            ];
        }

        return $recommendations;
    }

    /**
     * Get compliance statistics.
     */
    public function getComplianceStatistics($days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $auditLogs = AuditLog::complianceRelated()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $securityLogs = SecurityLog::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('severity', [SecurityLog::SEVERITY_HIGH, SecurityLog::SEVERITY_CRITICAL])
            ->get();

        return [
            'total_compliance_events' => $auditLogs->count(),
            'security_incidents' => $securityLogs->count(),
            'compliance_standards' => $auditLogs->pluck('compliance_standard')->unique()->count(),
            'gdpr_events' => $auditLogs->where('compliance_standard', AuditLog::COMPLIANCE_GDPR)->count(),
            'hipaa_events' => $auditLogs->where('compliance_standard', AuditLog::COMPLIANCE_HIPAA)->count(),
            'sox_events' => $auditLogs->where('compliance_standard', AuditLog::COMPLIANCE_SOX)->count(),
            'pci_events' => $auditLogs->where('compliance_standard', AuditLog::COMPLIANCE_PCI)->count(),
            'iso27001_events' => $auditLogs->where('compliance_standard', AuditLog::COMPLIANCE_ISO27001)->count(),
            'violations_detected' => $this->countTotalViolations($auditLogs, $securityLogs),
            'compliance_score' => $this->calculateOverallComplianceScore($auditLogs, $securityLogs),
        ];
    }

    /**
     * Count total violations.
     */
    private function countTotalViolations($auditLogs, $securityLogs): int
    {
        $violations = 0;

        // Count unauthorized access
        $violations += $auditLogs->where('metadata->unauthorized', true)->count();
        
        // Count data breaches
        $violations += $auditLogs->where('metadata->breach_detected', true)->count();
        
        // Count security incidents
        $violations += $securityLogs->count();

        return $violations;
    }

    /**
     * Calculate overall compliance score.
     */
    private function calculateOverallComplianceScore($auditLogs, $securityLogs): float
    {
        $totalEvents = $auditLogs->count() + $securityLogs->count();
        if ($totalEvents === 0) return 100.0;

        $violations = $this->countTotalViolations($auditLogs, $securityLogs);
        
        return max(0, 100 - ($violations / $totalEvents) * 100);
    }

    /**
     * Export compliance report.
     */
    public function exportComplianceReport($standard, $startDate, $endDate, $format = 'json'): string
    {
        $report = $this->generateComplianceReport($standard, $startDate, $endDate);
        
        $filename = "compliance_report_{$standard}_{$startDate}_{$endDate}.{$format}";
        $filePath = storage_path('app/compliance-reports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        if ($format === 'json') {
            file_put_contents($filePath, json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $this->exportToCSV($report, $filePath);
        }
        
        return $filePath;
    }

    /**
     * Export report to CSV.
     */
    private function exportToCSV($report, $filePath): void
    {
        $handle = fopen($filePath, 'w');
        
        // Write header
        fputcsv($handle, ['Compliance Report', $report['standard']]);
        fputcsv($handle, ['Period', $report['period']['start'] . ' to ' . $report['period']['end']]);
        fputcsv($handle, ['Compliance Score', $report['compliance_score'] . '%']);
        fputcsv($handle, []);
        
        // Write summary
        fputcsv($handle, ['Summary']);
        foreach ($report['summary'] as $key => $value) {
            fputcsv($handle, [ucfirst(str_replace('_', ' ', $key)), $value]);
        }
        fputcsv($handle, []);
        
        // Write violations
        if (!empty($report['violations'])) {
            fputcsv($handle, ['Violations']);
            fputcsv($handle, ['Type', 'Count', 'Severity', 'Description']);
            foreach ($report['violations'] as $violation) {
                fputcsv($handle, [$violation['type'], $violation['count'], $violation['severity'], $violation['description']]);
            }
            fputcsv($handle, []);
        }
        
        // Write recommendations
        if (!empty($report['recommendations'])) {
            fputcsv($handle, ['Recommendations']);
            fputcsv($handle, ['Priority', 'Title', 'Description']);
            foreach ($report['recommendations'] as $recommendation) {
                fputcsv($handle, [$recommendation['priority'], $recommendation['title'], $recommendation['description']]);
            }
        }
        
        fclose($handle);
    }
}
