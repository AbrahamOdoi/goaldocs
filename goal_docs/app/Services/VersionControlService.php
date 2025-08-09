<?php

namespace App\Services;

use App\Models\File;
use App\Models\FileVersion;
use App\Models\RecentActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VersionControlService
{
    /**
     * Create a new version of a file
     */
    public function createVersion(File $file, $uploadedFile, string $changeNotes = null): FileVersion
    {
        $user = Auth::user();
        
        // Get current version number
        $currentVersion = $file->versions()->where('is_current', true)->first();
        $newVersionNumber = $currentVersion ? $currentVersion->version_number + 1 : 1;
        
        // Mark current version as not current
        if ($currentVersion) {
            $currentVersion->update(['is_current' => false]);
        }
        
        // Generate unique filename for new version
        $extension = $uploadedFile->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'files/' . $file->user_type . '/' . date('Y/m') . '/versions';
        
        // Store new version file
        $filePath = $uploadedFile->storeAs($path, $filename, 'local');
        
        // Calculate file hash
        $fileHash = hash_file('sha256', $uploadedFile->getPathname());
        
        // Create version record
        $version = FileVersion::create([
            'file_id' => $file->id,
            'version_number' => $newVersionNumber,
            'file_path' => $filePath,
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'file_hash' => $fileHash,
            'uploaded_by' => $user->id,
            'change_notes' => $changeNotes ?? "Version {$newVersionNumber} uploaded",
            'is_current' => true,
            'metadata' => [
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'upload_timestamp' => now()->toISOString(),
                'file_extension' => $extension,
            ]
        ]);
        
        // Update main file record
        $file->update([
            'file_path' => $filePath,
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'file_hash' => $fileHash,
            'last_modified_at' => now(),
        ]);
        
        // Log activity
        RecentActivity::log(
            $user->id,
            $user->type,
            $user->type_name,
            'version_create',
            $file->id,
            $version->id,
            [
                'version_number' => $newVersionNumber,
                'change_notes' => $changeNotes,
                'file_size' => $uploadedFile->getSize(),
            ]
        );
        
        return $version;
    }
    
    /**
     * Compare two versions of a file
     */
    public function compareVersions(FileVersion $version1, FileVersion $version2): array
    {
        $comparison = [
            'version1' => $this->getVersionInfo($version1),
            'version2' => $this->getVersionInfo($version2),
            'differences' => [],
            'similarity_percentage' => 0,
        ];
        
        // Compare file metadata
        $comparison['differences']['metadata'] = $this->compareMetadata($version1, $version2);
        
        // Compare file content if text-based
        if ($this->isTextBased($version1->mime_type) && $this->isTextBased($version2->mime_type)) {
            $comparison['differences']['content'] = $this->compareTextContent($version1, $version2);
            $comparison['similarity_percentage'] = $this->calculateSimilarity($version1, $version2);
        }
        
        // Compare file sizes
        $comparison['differences']['size'] = [
            'version1_size' => $version1->file_size,
            'version2_size' => $version2->file_size,
            'size_difference' => $version2->file_size - $version1->file_size,
            'size_change_percentage' => $version1->file_size > 0 ? 
                (($version2->file_size - $version1->file_size) / $version1->file_size) * 100 : 0,
        ];
        
        return $comparison;
    }
    
    /**
     * Rollback to a specific version
     */
    public function rollbackToVersion(File $file, FileVersion $targetVersion): bool
    {
        $user = Auth::user();
        
        try {
            // Get current version
            $currentVersion = $file->versions()->where('is_current', true)->first();
            
            if (!$currentVersion) {
                throw new \Exception('No current version found');
            }
            
            // Mark current version as not current
            $currentVersion->update(['is_current' => false]);
            
            // Mark target version as current
            $targetVersion->update(['is_current' => true]);
            
            // Update main file record
            $file->update([
                'file_path' => $targetVersion->file_path,
                'file_size' => $targetVersion->file_size,
                'mime_type' => $targetVersion->mime_type,
                'file_hash' => $targetVersion->file_hash,
                'last_modified_at' => now(),
            ]);
            
            // Log activity
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'version_rollback',
                $file->id,
                $targetVersion->id,
                [
                    'from_version' => $currentVersion->version_number,
                    'to_version' => $targetVersion->version_number,
                    'rollback_reason' => 'Manual rollback by user',
                ]
            );
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error rolling back file {$file->id} to version {$targetVersion->id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get version history for a file
     */
    public function getVersionHistory(File $file): array
    {
        $versions = $file->versions()
            ->with('uploader')
            ->orderBy('version_number', 'desc')
            ->get();
        
        $history = [];
        
        foreach ($versions as $version) {
            $history[] = [
                'id' => $version->id,
                'version_number' => $version->version_number,
                'file_size' => $version->file_size,
                'human_size' => $this->formatFileSize($version->file_size),
                'mime_type' => $version->mime_type,
                'uploaded_by' => $version->uploader->name ?? 'Unknown',
                'uploaded_at' => $version->created_at->format('M j, Y g:i A'),
                'change_notes' => $version->change_notes,
                'is_current' => $version->is_current,
                'download_url' => route('files.version.download', $version),
                'preview_url' => route('files.version.preview', $version),
                'metadata' => $version->metadata ?? [],
            ];
        }
        
        return $history;
    }
    
    /**
     * Delete a specific version
     */
    public function deleteVersion(FileVersion $version): bool
    {
        $user = Auth::user();
        
        try {
            // Don't allow deletion of current version
            if ($version->is_current) {
                throw new \Exception('Cannot delete current version');
            }
            
            // Delete physical file
            if (Storage::disk('local')->exists($version->file_path)) {
                Storage::disk('local')->delete($version->file_path);
            }
            
            // Log activity before deletion
            RecentActivity::log(
                $user->id,
                $user->type,
                $user->type_name,
                'version_delete',
                $version->file_id,
                $version->id,
                [
                    'version_number' => $version->version_number,
                    'file_size' => $version->file_size,
                    'deletion_reason' => 'Manual deletion by user',
                ]
            );
            
            // Delete version record
            $version->delete();
            
            return true;
        } catch (\Exception $e) {
            Log::error("Error deleting version {$version->id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get version information
     */
    private function getVersionInfo(FileVersion $version): array
    {
        return [
            'id' => $version->id,
            'version_number' => $version->version_number,
            'file_size' => $version->file_size,
            'human_size' => $this->formatFileSize($version->file_size),
            'mime_type' => $version->mime_type,
            'uploaded_by' => $version->uploader->name ?? 'Unknown',
            'uploaded_at' => $version->created_at->format('M j, Y g:i A'),
            'change_notes' => $version->change_notes,
            'is_current' => $version->is_current,
            'file_hash' => $version->file_hash,
            'metadata' => $version->metadata ?? [],
        ];
    }
    
    /**
     * Compare metadata between versions
     */
    private function compareMetadata(FileVersion $version1, FileVersion $version2): array
    {
        $differences = [];
        
        // Compare file sizes
        if ($version1->file_size !== $version2->file_size) {
            $differences['file_size'] = [
                'version1' => $version1->file_size,
                'version2' => $version2->file_size,
                'difference' => $version2->file_size - $version1->file_size,
            ];
        }
        
        // Compare MIME types
        if ($version1->mime_type !== $version2->mime_type) {
            $differences['mime_type'] = [
                'version1' => $version1->mime_type,
                'version2' => $version2->mime_type,
            ];
        }
        
        // Compare file hashes
        if ($version1->file_hash !== $version2->file_hash) {
            $differences['file_hash'] = [
                'version1' => $version1->file_hash,
                'version2' => $version2->file_hash,
                'identical' => false,
            ];
        } else {
            $differences['file_hash'] = [
                'identical' => true,
            ];
        }
        
        return $differences;
    }
    
    /**
     * Compare text content between versions
     */
    private function compareTextContent(FileVersion $version1, FileVersion $version2): array
    {
        try {
            $content1 = $this->extractTextContent($version1);
            $content2 = $this->extractTextContent($version2);
            
            $differences = [
                'content_length' => [
                    'version1' => strlen($content1),
                    'version2' => strlen($content2),
                    'difference' => strlen($content2) - strlen($content1),
                ],
                'line_count' => [
                    'version1' => substr_count($content1, "\n") + 1,
                    'version2' => substr_count($content2, "\n") + 1,
                    'difference' => (substr_count($content2, "\n") + 1) - (substr_count($content1, "\n") + 1),
                ],
                'identical' => $content1 === $content2,
            ];
            
            // Generate diff if content is different
            if (!$differences['identical']) {
                $differences['diff'] = $this->generateDiff($content1, $content2);
            }
            
            return $differences;
        } catch (\Exception $e) {
            Log::error("Error comparing text content: " . $e->getMessage());
            return ['error' => 'Unable to compare content'];
        }
    }
    
    /**
     * Extract text content from version
     */
    private function extractTextContent(FileVersion $version): string
    {
        try {
            $path = Storage::disk('local')->path($version->file_path);
            
            switch ($version->mime_type) {
                case 'text/plain':
                    return file_get_contents($path);
                    
                case 'application/pdf':
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($path);
                    return $pdf->getText();
                    
                case 'application/msword':
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    return $this->extractTextFromWord($path);
                    
                default:
                    return '';
            }
        } catch (\Exception $e) {
            Log::error("Error extracting text from version {$version->id}: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Extract text from Word documents
     */
    private function extractTextFromWord(string $path): string
    {
        try {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            
            if ($extension === 'docx') {
                $zip = new \ZipArchive();
                if ($zip->open($path) === true) {
                    $content = $zip->getFromName('word/document.xml');
                    $zip->close();
                    
                    if ($content) {
                        return strip_tags($content);
                    }
                }
            }
            
            return '';
        } catch (\Exception $e) {
            Log::error("Error extracting text from Word document: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Generate diff between two text contents
     */
    private function generateDiff(string $content1, string $content2): array
    {
        // Simple line-by-line diff
        $lines1 = explode("\n", $content1);
        $lines2 = explode("\n", $content2);
        
        $diff = [];
        $maxLines = max(count($lines1), count($lines2));
        
        for ($i = 0; $i < $maxLines; $i++) {
            $line1 = $lines1[$i] ?? '';
            $line2 = $lines2[$i] ?? '';
            
            if ($line1 !== $line2) {
                $diff[] = [
                    'line_number' => $i + 1,
                    'version1' => $line1,
                    'version2' => $line2,
                    'type' => $line1 === '' ? 'added' : ($line2 === '' ? 'deleted' : 'modified'),
                ];
            }
        }
        
        return $diff;
    }
    
    /**
     * Calculate similarity percentage between versions
     */
    private function calculateSimilarity(FileVersion $version1, FileVersion $version2): float
    {
        try {
            $content1 = $this->extractTextContent($version1);
            $content2 = $this->extractTextContent($version2);
            
            if (empty($content1) && empty($content2)) {
                return 100.0;
            }
            
            if (empty($content1) || empty($content2)) {
                return 0.0;
            }
            
            // Use similar_text for similarity calculation
            similar_text($content1, $content2, $percentage);
            
            return round($percentage, 2);
        } catch (\Exception $e) {
            Log::error("Error calculating similarity: " . $e->getMessage());
            return 0.0;
        }
    }
    
    /**
     * Check if file type is text-based
     */
    private function isTextBased(string $mimeType): bool
    {
        $textTypes = [
            'text/plain',
            'text/html',
            'text/css',
            'text/javascript',
            'application/json',
            'application/xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        return in_array($mimeType, $textTypes);
    }
    
    /**
     * Format file size for display
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get version statistics
     */
    public function getVersionStats(File $file): array
    {
        $versions = $file->versions();
        
        return [
            'total_versions' => $versions->count(),
            'current_version' => $versions->where('is_current', true)->first()->version_number ?? 1,
            'total_size' => $versions->sum('file_size'),
            'average_size' => $versions->avg('file_size'),
            'largest_version' => $versions->orderBy('file_size', 'desc')->first(),
            'oldest_version' => $versions->orderBy('created_at', 'asc')->first(),
            'recent_activity' => $versions->orderBy('created_at', 'desc')->limit(5)->get(),
        ];
    }
} 