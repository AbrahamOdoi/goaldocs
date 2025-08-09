<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\SecurityAudit;
use App\Models\EncryptionKey;
use App\Services\SecurityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageSecurity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:manage {action} {--file-id= : Specific file ID} {--older-than=90 : Clean up data older than X days} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage security features - cleanup, statistics, and maintenance';

    /**
     * Execute the console command.
     */
    public function handle(SecurityService $securityService)
    {
        $action = $this->argument('action');
        $fileId = $this->option('file-id');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');
        
        switch ($action) {
            case 'stats':
                $this->showSecurityStats($fileId, $securityService);
                break;
                
            case 'cleanup':
                $this->cleanupSecurityData($fileId, $olderThan, $dryRun, $securityService);
                break;
                
            case 'audit':
                $this->manageAuditLogs($dryRun, $securityService);
                break;
                
            case 'keys':
                $this->manageEncryptionKeys($dryRun);
                break;
                
            case 'report':
                $this->generateSecurityReport($securityService);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: stats, cleanup, audit, keys, report");
                return 1;
        }
        
        return 0;
    }
    
    /**
     * Show security statistics
     */
    private function showSecurityStats($fileId = null, SecurityService $securityService)
    {
        $this->info('Security Statistics');
        $this->info('==================');
        
        if ($fileId) {
            $file = File::find($fileId);
            if (!$file) {
                $this->error("File with ID {$fileId} not found");
                return;
            }
            
            $status = $securityService->getFileSecurityStatus($file);
            $this->info("File: {$file->name}");
            $this->info("Encrypted: " . ($status['encrypted'] ? 'Yes' : 'No'));
            $this->info("Watermarked: " . ($status['watermarked'] ? 'Yes' : 'No'));
            $this->info("Access Logged: " . ($status['access_logged'] ? 'Yes' : 'No'));
            $this->info("Security Score: {$status['security_score']}/100");
        } else {
            $stats = $securityService->getSecurityStats();
            $this->info("Total files: {$stats['total_files']}");
            $this->info("Encrypted files: {$stats['encrypted_files']}");
            $this->info("Watermarked files: {$stats['watermarked_files']}");
            $this->info("Security audits: {$stats['security_audits']}");
            $this->info("Recent audits (7 days): {$stats['recent_audits']}");
            $this->info("Failed access attempts: {$stats['failed_access_attempts']}");
            $this->info("Average security score: {$stats['average_security_score']}/100");
            
            // Top files by security score
            $files = File::all();
            $topFiles = collect();
            
            foreach ($files as $file) {
                $status = $securityService->getFileSecurityStatus($file);
                $topFiles->push([
                    'name' => $file->name,
                    'score' => $status['security_score'],
                    'encrypted' => $status['encrypted'],
                    'watermarked' => $status['watermarked'],
                ]);
            }
            
            $topFiles = $topFiles->sortByDesc('score')->take(10);
            
            if ($topFiles->count() > 0) {
                $this->newLine();
                $this->info('Top files by security score:');
                foreach ($topFiles as $file) {
                    $this->info("  - {$file['name']}: {$file['score']}/100 (E: " . ($file['encrypted'] ? 'Y' : 'N') . ", W: " . ($file['watermarked'] ? 'Y' : 'N') . ")");
                }
            }
        }
    }
    
    /**
     * Cleanup security data
     */
    private function cleanupSecurityData($fileId, $olderThan, $dryRun, SecurityService $securityService)
    {
        $this->info('Cleaning up security data...');
        
        // Cleanup audit logs
        $auditCount = $securityService->cleanupAuditLogs($olderThan);
        
        if ($dryRun) {
            $this->info("Would clean up {$auditCount} old audit logs");
        } else {
            $this->info("Cleaned up {$auditCount} old audit logs");
        }
        
        // Cleanup old encryption keys (optional)
        $oldKeys = EncryptionKey::where('created_at', '<', now()->subDays($olderThan * 2))->get();
        $keyCount = $oldKeys->count();
        
        if ($dryRun) {
            $this->info("Would clean up {$keyCount} old encryption keys");
        } else {
            foreach ($oldKeys as $key) {
                // Only delete if file no longer exists
                if (!$key->file) {
                    $key->delete();
                    $this->info("Deleted orphaned encryption key {$key->id}");
                }
            }
        }
    }
    
    /**
     * Manage audit logs
     */
    private function manageAuditLogs($dryRun, SecurityService $securityService)
    {
        $this->info('Managing security audit logs...');
        
        // Show recent security events
        $recentEvents = SecurityAudit::securityEvents()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        if ($recentEvents->count() > 0) {
            $this->newLine();
            $this->info('Recent security events:');
            foreach ($recentEvents as $event) {
                $severity = strtoupper($event->severity);
                $this->info("  - [{$severity}] {$event->action_display}: {$event->description} (User: {$event->user->name}, {$event->formatted_timestamp})");
            }
        } else {
            $this->info('No recent security events');
        }
        
        // Show failed access attempts
        $failedAttempts = SecurityAudit::failedAccess()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        if ($failedAttempts->count() > 0) {
            $this->newLine();
            $this->info('Recent failed access attempts:');
            foreach ($failedAttempts as $attempt) {
                $this->info("  - {$attempt->description} (User: {$attempt->user->name}, IP: {$attempt->ip_address}, {$attempt->formatted_timestamp})");
            }
        }
        
        // Show audit statistics
        $totalAudits = SecurityAudit::count();
        $recentAudits = SecurityAudit::recent(7)->count();
        $highSeverity = SecurityAudit::where('action', 'access_denied')->count();
        
        $this->newLine();
        $this->info('Audit Statistics:');
        $this->info("Total audits: {$totalAudits}");
        $this->info("Recent audits (7 days): {$recentAudits}");
        $this->info("High severity events: {$highSeverity}");
        
        // Show audits by action type
        $auditsByAction = SecurityAudit::selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();
            
        if ($auditsByAction->count() > 0) {
            $this->newLine();
            $this->info('Audits by action type:');
            foreach ($auditsByAction as $audit) {
                $this->info("  - {$audit->action}: {$audit->count}");
            }
        }
    }
    
    /**
     * Manage encryption keys
     */
    private function manageEncryptionKeys($dryRun)
    {
        $this->info('Managing encryption keys...');
        
        $keys = EncryptionKey::with(['file', 'creator'])->get();
        
        if ($keys->count() > 0) {
            $this->info('Encryption Keys:');
            foreach ($keys as $key) {
                $status = $key->should_rotate ? 'NEEDS ROTATION' : 'OK';
                $this->info("  - Key {$key->id}: {$key->encryption_method} (File: {$key->file->name}, Age: {$key->age_in_days} days, Status: {$status})");
            }
        } else {
            $this->info('No encryption keys found');
        }
        
        // Show keys that need rotation
        $keysNeedingRotation = EncryptionKey::where('created_at', '<', now()->subDays(90))->get();
        
        if ($keysNeedingRotation->count() > 0) {
            $this->newLine();
            $this->warn("Keys needing rotation ({$keysNeedingRotation->count()}):");
            foreach ($keysNeedingRotation as $key) {
                $this->warn("  - Key {$key->id}: {$key->file->name} (Age: {$key->age_in_days} days)");
            }
        }
        
        // Show encryption statistics
        $totalKeys = EncryptionKey::count();
        $aes256Keys = EncryptionKey::byMethod('AES-256-CBC')->count();
        $aes128Keys = EncryptionKey::byMethod('AES-128-CBC')->count();
        $recentKeys = EncryptionKey::recent(30)->count();
        
        $this->newLine();
        $this->info('Encryption Statistics:');
        $this->info("Total keys: {$totalKeys}");
        $this->info("AES-256-CBC keys: {$aes256Keys}");
        $this->info("AES-128-CBC keys: {$aes128Keys}");
        $this->info("Recent keys (30 days): {$recentKeys}");
    }
    
    /**
     * Generate security report
     */
    private function generateSecurityReport(SecurityService $securityService)
    {
        $this->info('Generating security report...');
        
        $report = $securityService->generateSecurityReport();
        
        $this->newLine();
        $this->info('Security Report');
        $this->info('==============');
        $this->info("Generated: {$report['generated_at']}");
        
        $this->newLine();
        $this->info('Overall Statistics:');
        $this->info("Total files: {$report['stats']['total_files']}");
        $this->info("Encrypted files: {$report['stats']['encrypted_files']}");
        $this->info("Watermarked files: {$report['stats']['watermarked_files']}");
        $this->info("Security audits: {$report['stats']['security_audits']}");
        $this->info("Average security score: {$report['stats']['average_security_score']}/100");
        
        $this->newLine();
        $this->info('Recent Security Events:');
        foreach (array_slice($report['recent_audits'], 0, 10) as $audit) {
            $this->info("  - {$audit['action']}: {$audit['description']} (User: {$audit['user']['name']}, {$audit['timestamp']})");
        }
        
        $this->newLine();
        $this->info('Audit Events by Type:');
        foreach ($report['audit_by_action'] as $action => $count) {
            $this->info("  - {$action}: {$count}");
        }
        
        $this->newLine();
        $this->info('Top Users by Security Actions:');
        foreach ($report['top_users'] as $user) {
            $this->info("  - {$user['user']}: {$user['action_count']} actions");
        }
        
        $this->newLine();
        $this->info('Security Recommendations:');
        
        if ($report['stats']['average_security_score'] < 50) {
            $this->warn("  - Consider encrypting more files to improve security");
        }
        
        if ($report['stats']['failed_access_attempts'] > 10) {
            $this->warn("  - High number of failed access attempts detected");
        }
        
        if (count($report['recent_audits']) > 100) {
            $this->info("  - High security activity detected");
        }
        
        $this->info("  - Regular security audits are recommended");
    }
}
