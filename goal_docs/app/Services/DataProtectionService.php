<?php

namespace App\Services;

use App\Models\User;
use App\Models\File;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DataProtectionService
{
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Process data subject rights request (GDPR).
     */
    public function processDataSubjectRequest($userId, $requestType, $data = []): array
    {
        $user = User::findOrFail($userId);
        
        switch ($requestType) {
            case 'access':
                return $this->processAccessRequest($user);
            case 'rectification':
                return $this->processRectificationRequest($user, $data);
            case 'erasure':
                return $this->processErasureRequest($user);
            case 'portability':
                return $this->processPortabilityRequest($user);
            case 'restriction':
                return $this->processRestrictionRequest($user, $data);
            case 'objection':
                return $this->processObjectionRequest($user, $data);
            default:
                throw new \InvalidArgumentException('Invalid request type');
        }
    }

    /**
     * Process data access request (GDPR Article 15).
     */
    private function processAccessRequest($user): array
    {
        // Get all personal data
        $personalData = [
            'profile' => $this->getUserProfileData($user),
            'files' => $this->getUserFilesData($user),
            'activity' => $this->getUserActivityData($user),
            'consents' => $this->getUserConsentsData($user),
        ];

        // Log the access request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_READ,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Access Request',
            ['request_type' => 'access', 'data_scope' => array_keys($personalData)]
        );

        return [
            'success' => true,
            'message' => 'Data access request processed successfully',
            'data' => $personalData,
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Process data rectification request (GDPR Article 16).
     */
    private function processRectificationRequest($user, $data): array
    {
        $updatedFields = [];

        // Update user profile data
        if (isset($data['profile'])) {
            foreach ($data['profile'] as $field => $value) {
                if (in_array($field, ['name', 'email', 'phone'])) {
                    $user->update([$field => $value]);
                    $updatedFields[] = $field;
                }
            }
        }

        // Log the rectification request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_UPDATE,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Rectification Request',
            ['request_type' => 'rectification', 'updated_fields' => $updatedFields]
        );

        return [
            'success' => true,
            'message' => 'Data rectification request processed successfully',
            'updated_fields' => $updatedFields,
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Process data erasure request (GDPR Article 17).
     */
    private function processErasureRequest($user): array
    {
        // Check if erasure is possible (no legal obligations)
        if (!$this->canErasureBeProcessed($user)) {
            return [
                'success' => false,
                'message' => 'Data erasure cannot be processed due to legal obligations',
                'reason' => 'Legal retention requirements',
            ];
        }

        // Anonymize personal data instead of deletion
        $anonymizedData = $this->anonymizeUserData($user);

        // Log the erasure request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_DELETE,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Erasure Request',
            ['request_type' => 'erasure', 'anonymized' => true]
        );

        return [
            'success' => true,
            'message' => 'Data erasure request processed successfully (data anonymized)',
            'anonymized_data' => $anonymizedData,
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Process data portability request (GDPR Article 20).
     */
    private function processPortabilityRequest($user): array
    {
        // Prepare data for export
        $exportData = [
            'profile' => $this->getUserProfileData($user),
            'files' => $this->getUserFilesData($user),
            'activity' => $this->getUserActivityData($user),
            'consents' => $this->getUserConsentsData($user),
        ];

        // Create export file
        $filename = 'data_export_' . $user->id . '_' . time() . '.json';
        $filePath = storage_path('app/data-exports/' . $filename);
        
        // Ensure directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, json_encode($exportData, JSON_PRETTY_PRINT));

        // Log the portability request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_EXPORT,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Portability Request',
            ['request_type' => 'portability', 'export_file' => $filename]
        );

        return [
            'success' => true,
            'message' => 'Data portability request processed successfully',
            'export_file' => $filename,
            'download_url' => route('data-protection.download-export', $filename),
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Process data restriction request (GDPR Article 18).
     */
    private function processRestrictionRequest($user, $data): array
    {
        $restrictedFields = $data['fields'] ?? [];

        // Apply data processing restrictions
        $this->applyDataRestrictions($user, $restrictedFields);

        // Log the restriction request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_UPDATE,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Restriction Request',
            ['request_type' => 'restriction', 'restricted_fields' => $restrictedFields]
        );

        return [
            'success' => true,
            'message' => 'Data restriction request processed successfully',
            'restricted_fields' => $restrictedFields,
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Process data objection request (GDPR Article 21).
     */
    private function processObjectionRequest($user, $data): array
    {
        $objectionType = $data['type'] ?? 'general';
        $objectionReason = $data['reason'] ?? '';

        // Record the objection
        $this->recordDataObjection($user, $objectionType, $objectionReason);

        // Log the objection request
        $this->securityService->logAuditEvent(
            $user->id,
            AuditLog::ACTION_UPDATE,
            AuditLog::RESOURCE_USER,
            $user->id,
            'GDPR Data Objection Request',
            ['request_type' => 'objection', 'objection_type' => $objectionType]
        );

        return [
            'success' => true,
            'message' => 'Data objection request processed successfully',
            'objection_type' => $objectionType,
            'request_id' => 'DSR-' . time() . '-' . $user->id,
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Get user profile data.
     */
    private function getUserProfileData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'created_at' => $user->created_at->toISOString(),
            'updated_at' => $user->updated_at->toISOString(),
            'last_login' => $user->last_login?->toISOString(),
            'is_admin' => $user->is_admin,
        ];
    }

    /**
     * Get user files data.
     */
    private function getUserFilesData($user): array
    {
        $files = File::where('user_id', $user->id)->get();
        
        return $files->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->name,
                'type' => $file->type,
                'size' => $file->size,
                'created_at' => $file->created_at->toISOString(),
                'updated_at' => $file->updated_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Get user activity data.
     */
    private function getUserActivityData($user): array
    {
        $auditLogs = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return $auditLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'resource_type' => $log->resource_type,
                'resource_name' => $log->resource_name,
                'description' => $log->description,
                'created_at' => $log->created_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Get user consents data.
     */
    private function getUserConsentsData($user): array
    {
        // This would typically come from a consent management system
        return [
            'marketing_emails' => true,
            'data_processing' => true,
            'third_party_sharing' => false,
            'consent_date' => $user->created_at->toISOString(),
            'last_updated' => $user->updated_at->toISOString(),
        ];
    }

    /**
     * Check if data erasure can be processed.
     */
    private function canErasureBeProcessed($user): bool
    {
        // Check for legal obligations (e.g., financial records, audit requirements)
        $hasFinancialData = File::where('user_id', $user->id)
            ->where('type', 'financial')
            ->exists();

        $hasRecentActivity = AuditLog::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        // Cannot erase if there are legal obligations
        return !($hasFinancialData || $hasRecentActivity);
    }

    /**
     * Anonymize user data.
     */
    private function anonymizeUserData($user): array
    {
        $anonymizedData = [
            'original_id' => $user->id,
            'anonymized_at' => now()->toISOString(),
        ];

        // Anonymize user profile
        $user->update([
            'name' => 'ANONYMIZED_' . $user->id,
            'email' => 'anonymized_' . $user->id . '@deleted.local',
            'phone' => null,
        ]);

        // Anonymize files
        File::where('user_id', $user->id)->update([
            'name' => 'ANONYMIZED_FILE',
        ]);

        return $anonymizedData;
    }

    /**
     * Apply data processing restrictions.
     */
    private function applyDataRestrictions($user, $fields): void
    {
        // Store restriction information
        Cache::put("data_restriction_{$user->id}", $fields, 86400 * 365); // 1 year
    }

    /**
     * Record data processing objection.
     */
    private function recordDataObjection($user, $type, $reason): void
    {
        // Store objection information
        Cache::put("data_objection_{$user->id}", [
            'type' => $type,
            'reason' => $reason,
            'recorded_at' => now()->toISOString(),
        ], 86400 * 365); // 1 year
    }

    /**
     * Implement data retention policies.
     */
    public function applyDataRetentionPolicies(): array
    {
        $results = [
            'processed' => 0,
            'deleted' => 0,
            'archived' => 0,
            'errors' => 0,
        ];

        // Apply file retention policies
        $this->applyFileRetentionPolicies($results);

        // Apply audit log retention policies
        $this->applyAuditLogRetentionPolicies($results);

        // Apply user data retention policies
        $this->applyUserDataRetentionPolicies($results);

        return $results;
    }

    /**
     * Apply file retention policies.
     */
    private function applyFileRetentionPolicies(&$results): void
    {
        $retentionDays = config('data-protection.file_retention_days', 2555); // 7 years
        $cutoffDate = now()->subDays($retentionDays);

        $oldFiles = File::where('created_at', '<', $cutoffDate)
            ->where('type', '!=', 'financial') // Keep financial files longer
            ->get();

        foreach ($oldFiles as $file) {
            try {
                // Archive file instead of deletion
                $this->archiveFile($file);
                $results['archived']++;
            } catch (\Exception $e) {
                $results['errors']++;
            }
            $results['processed']++;
        }
    }

    /**
     * Apply audit log retention policies.
     */
    private function applyAuditLogRetentionPolicies(&$results): void
    {
        $retentionDays = config('data-protection.audit_retention_days', 1825); // 5 years
        $cutoffDate = now()->subDays($retentionDays);

        $oldAuditLogs = AuditLog::where('created_at', '<', $cutoffDate)
            ->where('is_compliance_related', false) // Keep compliance logs longer
            ->get();

        foreach ($oldAuditLogs as $log) {
            try {
                $log->delete();
                $results['deleted']++;
            } catch (\Exception $e) {
                $results['errors']++;
            }
            $results['processed']++;
        }
    }

    /**
     * Apply user data retention policies.
     */
    private function applyUserDataRetentionPolicies(&$results): void
    {
        $retentionDays = config('data-protection.user_retention_days', 3650); // 10 years
        $cutoffDate = now()->subDays($retentionDays);

        $inactiveUsers = User::where('last_login', '<', $cutoffDate)
            ->where('is_admin', false) // Keep admin accounts
            ->get();

        foreach ($inactiveUsers as $user) {
            try {
                $this->anonymizeUserData($user);
                $results['archived']++;
            } catch (\Exception $e) {
                $results['errors']++;
            }
            $results['processed']++;
        }
    }

    /**
     * Archive a file.
     */
    private function archiveFile($file): void
    {
        // Move file to archive storage
        $archivePath = 'archives/' . date('Y/m/') . $file->filename;
        
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('archive')->put($archivePath, 
                Storage::disk('local')->get($file->file_path)
            );
        }

        // Update file record
        $file->update([
            'file_path' => $archivePath,
            'archived_at' => now(),
            'storage_disk' => 'archive',
        ]);
    }

    /**
     * Get data protection statistics.
     */
    public function getDataProtectionStatistics(): array
    {
        $totalUsers = User::count();
        $totalFiles = File::count();
        $totalAuditLogs = AuditLog::count();

        $archivedFiles = File::whereNotNull('archived_at')->count();
        $anonymizedUsers = User::where('name', 'like', 'ANONYMIZED_%')->count();

        $recentDSRRequests = $this->getRecentDSRRequests();

        return [
            'total_users' => $totalUsers,
            'total_files' => $totalFiles,
            'total_audit_logs' => $totalAuditLogs,
            'archived_files' => $archivedFiles,
            'anonymized_users' => $anonymizedUsers,
            'recent_dsr_requests' => $recentDSRRequests,
            'retention_policies' => [
                'file_retention_days' => config('data-protection.file_retention_days', 2555),
                'audit_retention_days' => config('data-protection.audit_retention_days', 1825),
                'user_retention_days' => config('data-protection.user_retention_days', 3650),
            ],
        ];
    }

    /**
     * Get recent data subject rights requests.
     */
    private function getRecentDSRRequests(): array
    {
        $recentLogs = AuditLog::where('description', 'like', '%GDPR%')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return $recentLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'request_type' => $this->extractDSRRequestType($log->description),
                'created_at' => $log->created_at->toISOString(),
            ];
        })->toArray();
    }

    /**
     * Extract DSR request type from description.
     */
    private function extractDSRRequestType($description): string
    {
        if (strpos($description, 'Access') !== false) return 'access';
        if (strpos($description, 'Rectification') !== false) return 'rectification';
        if (strpos($description, 'Erasure') !== false) return 'erasure';
        if (strpos($description, 'Portability') !== false) return 'portability';
        if (strpos($description, 'Restriction') !== false) return 'restriction';
        if (strpos($description, 'Objection') !== false) return 'objection';
        
        return 'unknown';
    }

    /**
     * Check data processing consent.
     */
    public function checkDataProcessingConsent($userId, $purpose): bool
    {
        // This would typically check against a consent management system
        $consent = Cache::get("consent_{$userId}_{$purpose}", false);
        
        return $consent;
    }

    /**
     * Record data processing consent.
     */
    public function recordDataProcessingConsent($userId, $purpose, $consent = true): void
    {
        Cache::put("consent_{$userId}_{$purpose}", $consent, 86400 * 365); // 1 year
        
        // Log consent
        $this->securityService->logAuditEvent(
            $userId,
            AuditLog::ACTION_UPDATE,
            AuditLog::RESOURCE_USER,
            $userId,
            'Data Processing Consent Updated',
            ['purpose' => $purpose, 'consent' => $consent]
        );
    }
}
