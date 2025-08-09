<?php

namespace App\Services;

use App\Models\File;
use App\Models\SecurityAudit;
use App\Models\EncryptionKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Faker\Factory as Faker;

class SecurityService
{
    /**
     * Encrypt a file
     */
    public function encryptFile(File $file, string $password = null): array
    {
        $user = Auth::user();
        
        // Generate encryption key if password not provided
        if (!$password) {
            $password = $this->generateStrongPassword();
        }
        
        // Read file content
        $filePath = storage_path('app/' . $file->file_path);
        if (!file_exists($filePath)) {
            throw new \Exception('File not found on disk');
        }
        
        $fileContent = file_get_contents($filePath);
        
        // Encrypt content
        $encryptedContent = Crypt::encryptString($fileContent);
        
        // Generate encrypted file path
        $encryptedPath = 'encrypted/' . Str::random(40) . '.enc';
        
        // Store encrypted content
        Storage::put($encryptedPath, $encryptedContent);
        
        // Store encryption key securely
        $encryptionKey = EncryptionKey::create([
            'file_id' => $file->id,
            'key_hash' => hash('sha256', $password),
            'encryption_method' => 'AES-256-CBC',
            'created_by' => $user->id,
            'metadata' => [
                'original_path' => $file->file_path,
                'encrypted_path' => $encryptedPath,
                'file_size' => strlen($fileContent),
                'encrypted_size' => strlen($encryptedContent),
            ]
        ]);
        
        // Update file metadata
        $file->update([
            'metadata' => array_merge($file->metadata ?? [], [
                'encrypted' => true,
                'encryption_key_id' => $encryptionKey->id,
                'encrypted_at' => now()->toISOString(),
                'encrypted_by' => $user->id,
            ])
        ]);
        
        // Log security audit
        $this->logSecurityAudit(
            $user->id,
            'file_encrypt',
            $file->id,
            'File encrypted successfully',
            [
                'encryption_method' => 'AES-256-CBC',
                'file_size' => strlen($fileContent),
                'encrypted_size' => strlen($encryptedContent),
            ]
        );
        
        return [
            'success' => true,
            'encrypted_path' => $encryptedPath,
            'password' => $password,
            'key_id' => $encryptionKey->id,
        ];
    }
    
    /**
     * Decrypt a file
     */
    public function decryptFile(File $file, string $password): array
    {
        $user = Auth::user();
        
        // Verify encryption key
        $encryptionKey = EncryptionKey::where('file_id', $file->id)
            ->where('key_hash', hash('sha256', $password))
            ->first();
            
        if (!$encryptionKey) {
            throw new \Exception('Invalid encryption password');
        }
        
        // Read encrypted content
        $encryptedPath = $encryptionKey->metadata['encrypted_path'];
        $encryptedContent = Storage::get($encryptedPath);
        
        if (!$encryptedContent) {
            throw new \Exception('Encrypted file not found');
        }
        
        // Decrypt content
        try {
            $decryptedContent = Crypt::decryptString($encryptedContent);
        } catch (\Exception $e) {
            throw new \Exception('Failed to decrypt file: Invalid password or corrupted data');
        }
        
        // Generate decrypted file path
        $decryptedPath = 'decrypted/' . Str::random(40) . '_' . $file->name;
        
        // Store decrypted content
        Storage::put($decryptedPath, $decryptedContent);
        
        // Log security audit
        $this->logSecurityAudit(
            $user->id,
            'file_decrypt',
            $file->id,
            'File decrypted successfully',
            [
                'decrypted_path' => $decryptedPath,
                'file_size' => strlen($decryptedContent),
            ]
        );
        
        return [
            'success' => true,
            'decrypted_path' => $decryptedPath,
            'file_size' => strlen($decryptedContent),
        ];
    }
    
    /**
     * Add watermark to file
     */
    public function addWatermark(File $file, array $options = []): array
    {
        $user = Auth::user();
        
        // Default watermark options
        $defaultOptions = [
            'text' => $user->name . ' - ' . now()->format('Y-m-d H:i:s'),
            'position' => 'bottom-right',
            'opacity' => 0.3,
            'font_size' => 16,
            'color' => '#000000',
            'rotation' => -45,
        ];
        
        $options = array_merge($defaultOptions, $options);
        
        // Check if file is an image
        if (!$this->isImageFile($file)) {
            throw new \Exception('Watermarking is only supported for image files');
        }
        
        // Load image
        $filePath = storage_path('app/' . $file->file_path);
        $image = Image::make($filePath);
        
        // Add watermark
        $this->applyWatermark($image, $options);
        
        // Generate watermarked file path
        $watermarkedPath = 'watermarked/' . Str::random(40) . '_' . $file->name;
        
        // Save watermarked image
        $image->save(storage_path('app/' . $watermarkedPath));
        
        // Update file metadata
        $file->update([
            'metadata' => array_merge($file->metadata ?? [], [
                'watermarked' => true,
                'watermarked_at' => now()->toISOString(),
                'watermarked_by' => $user->id,
                'watermark_options' => $options,
                'watermarked_path' => $watermarkedPath,
            ])
        ]);
        
        // Log security audit
        $this->logSecurityAudit(
            $user->id,
            'file_watermark',
            $file->id,
            'Watermark added to file',
            [
                'watermark_text' => $options['text'],
                'watermark_position' => $options['position'],
                'watermarked_path' => $watermarkedPath,
            ]
        );
        
        return [
            'success' => true,
            'watermarked_path' => $watermarkedPath,
            'options' => $options,
        ];
    }
    
    /**
     * Apply watermark to image
     */
    private function applyWatermark($image, array $options): void
    {
        $width = $image->width();
        $height = $image->height();
        
        // Calculate position
        $position = $this->calculateWatermarkPosition($width, $height, $options['position']);
        
        // Add text watermark
        $image->text($options['text'], $position['x'], $position['y'], function ($font) use ($options) {
            $font->file(storage_path('app/fonts/arial.ttf'));
            $font->size($options['font_size']);
            $font->color($options['color']);
            $font->align('center');
            $font->valign('middle');
            $font->angle($options['rotation']);
            $font->opacity($options['opacity']);
        });
    }
    
    /**
     * Calculate watermark position
     */
    private function calculateWatermarkPosition(int $width, int $height, string $position): array
    {
        $padding = 20;
        
        switch ($position) {
            case 'top-left':
                return ['x' => $padding, 'y' => $padding];
            case 'top-right':
                return ['x' => $width - $padding, 'y' => $padding];
            case 'bottom-left':
                return ['x' => $padding, 'y' => $height - $padding];
            case 'bottom-right':
                return ['x' => $width - $padding, 'y' => $height - $padding];
            case 'center':
                return ['x' => $width / 2, 'y' => $height / 2];
            default:
                return ['x' => $width - $padding, 'y' => $height - $padding];
        }
    }
    
    /**
     * Check if file is an image
     */
    private function isImageFile(File $file): bool
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
        return in_array($file->mime_type, $imageTypes);
    }
    
    /**
     * Generate strong password
     */
    private function generateStrongPassword(int $length = 16): string
    {
        $faker = Faker::create();
        return $faker->password($length, $length) . $faker->randomLetter() . $faker->randomDigit();
    }
    
    /**
     * Log security audit
     */
    private function logSecurityAudit(int $userId, string $action, int $fileId, string $description, array $metadata = []): void
    {
        SecurityAudit::create([
            'user_id' => $userId,
            'action' => $action,
            'resource_type' => 'file',
            'resource_id' => $fileId,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }
    
    /**
     * Get file security status
     */
    public function getFileSecurityStatus(File $file): array
    {
        $status = [
            'encrypted' => false,
            'watermarked' => false,
            'access_logged' => false,
            'security_score' => 0,
        ];
        
        // Check encryption
        if (isset($file->metadata['encrypted']) && $file->metadata['encrypted']) {
            $status['encrypted'] = true;
            $status['security_score'] += 40;
        }
        
        // Check watermarking
        if (isset($file->metadata['watermarked']) && $file->metadata['watermarked']) {
            $status['watermarked'] = true;
            $status['security_score'] += 30;
        }
        
        // Check access logging
        $accessLogs = SecurityAudit::where('resource_id', $file->id)
            ->where('action', 'file_access')
            ->count();
            
        if ($accessLogs > 0) {
            $status['access_logged'] = true;
            $status['security_score'] += 20;
        }
        
        // Additional security points
        if ($file->metadata && isset($file->metadata['permissions'])) {
            $status['security_score'] += 10;
        }
        
        return $status;
    }
    
    /**
     * Get security audit logs
     */
    public function getSecurityAuditLogs(int $fileId = null, int $limit = 50): array
    {
        $query = SecurityAudit::with('user');
        
        if ($fileId) {
            $query->where('resource_id', $fileId);
        }
        
        $logs = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
            
        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => [
                    'id' => $log->user->id,
                    'name' => $log->user->name,
                    'email' => $log->user->email,
                ],
                'timestamp' => $log->created_at->format('M j, Y g:i A'),
                'ip_address' => $log->ip_address,
                'metadata' => $log->metadata,
            ];
        })->toArray();
    }
    
    /**
     * Get security statistics
     */
    public function getSecurityStats(): array
    {
        $stats = [
            'total_files' => File::count(),
            'encrypted_files' => File::whereRaw("JSON_EXTRACT(metadata, '$.encrypted') = true")->count(),
            'watermarked_files' => File::whereRaw("JSON_EXTRACT(metadata, '$.watermarked') = true")->count(),
            'security_audits' => SecurityAudit::count(),
            'recent_audits' => SecurityAudit::where('created_at', '>', now()->subDays(7))->count(),
            'failed_access_attempts' => SecurityAudit::where('action', 'access_denied')->count(),
        ];
        
        // Calculate security score
        $totalScore = 0;
        $files = File::all();
        
        foreach ($files as $file) {
            $securityStatus = $this->getFileSecurityStatus($file);
            $totalScore += $securityStatus['security_score'];
        }
        
        $stats['average_security_score'] = $files->count() > 0 ? round($totalScore / $files->count(), 2) : 0;
        
        return $stats;
    }
    
    /**
     * Clean up old security audit logs
     */
    public function cleanupAuditLogs(int $olderThan = 90): int
    {
        $oldLogs = SecurityAudit::where('created_at', '<', now()->subDays($olderThan))->get();
        $count = $oldLogs->count();
        
        foreach ($oldLogs as $log) {
            $log->delete();
        }
        
        return $count;
    }
    
    /**
     * Generate security report
     */
    public function generateSecurityReport(): array
    {
        $stats = $this->getSecurityStats();
        $recentAudits = $this->getSecurityAuditLogs(null, 100);
        
        // Group audits by action
        $auditByAction = collect($recentAudits)->groupBy('action')->map(function ($group) {
            return $group->count();
        })->toArray();
        
        // Get top users by security actions
        $topUsers = SecurityAudit::selectRaw('user_id, COUNT(*) as action_count')
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('action_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($audit) {
                return [
                    'user' => $audit->user->name,
                    'action_count' => $audit->action_count,
                ];
            });
        
        return [
            'stats' => $stats,
            'recent_audits' => $recentAudits,
            'audit_by_action' => $auditByAction,
            'top_users' => $topUsers,
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
} 