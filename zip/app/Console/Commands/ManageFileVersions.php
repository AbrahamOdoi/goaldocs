<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\FileVersion;
use App\Services\VersionControlService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ManageFileVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:manage-versions {action} {--file-id= : Specific file ID} {--keep-versions=5 : Number of versions to keep} {--older-than=30 : Delete versions older than X days} {--dry-run : Show what would be done without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage file versions - cleanup, statistics, and maintenance';

    /**
     * Execute the console command.
     */
    public function handle(VersionControlService $versionService)
    {
        $action = $this->argument('action');
        $fileId = $this->option('file-id');
        $keepVersions = $this->option('keep-versions');
        $olderThan = $this->option('older-than');
        $dryRun = $this->option('dry-run');
        
        switch ($action) {
            case 'stats':
                $this->showVersionStats($fileId);
                break;
                
            case 'cleanup':
                $this->cleanupVersions($fileId, $keepVersions, $olderThan, $dryRun);
                break;
                
            case 'verify':
                $this->verifyVersions($fileId);
                break;
                
            case 'orphaned':
                $this->findOrphanedVersions($dryRun);
                break;
                
            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: stats, cleanup, verify, orphaned");
                return 1;
        }
        
        return 0;
    }
    
    /**
     * Show version statistics
     */
    private function showVersionStats($fileId = null)
    {
        $this->info('File Version Statistics');
        $this->info('=====================');
        
        if ($fileId) {
            $file = File::find($fileId);
            if (!$file) {
                $this->error("File with ID {$fileId} not found");
                return;
            }
            
            $stats = $versionService->getVersionStats($file);
            $this->info("File: {$file->name}");
            $this->info("Total versions: {$stats['total_versions']}");
            $this->info("Current version: {$stats['current_version']}");
            $this->info("Total size: " . number_format($stats['total_size'] / 1024 / 1024, 2) . " MB");
            $this->info("Average size: " . number_format($stats['average_size'] / 1024 / 1024, 2) . " MB");
            
            if ($stats['largest_version']) {
                $this->info("Largest version: " . number_format($stats['largest_version']->file_size / 1024 / 1024, 2) . " MB");
            }
        } else {
            $totalFiles = File::count();
            $filesWithVersions = File::whereHas('versions')->count();
            $totalVersions = FileVersion::count();
            $totalSize = FileVersion::sum('file_size');
            
            $this->info("Total files: {$totalFiles}");
            $this->info("Files with versions: {$filesWithVersions}");
            $this->info("Total versions: {$totalVersions}");
            $this->info("Total version size: " . number_format($totalSize / 1024 / 1024, 2) . " MB");
            
            // Top files by version count
            $topFiles = File::withCount('versions')
                ->orderBy('versions_count', 'desc')
                ->limit(10)
                ->get();
                
            if ($topFiles->count() > 0) {
                $this->newLine();
                $this->info('Top files by version count:');
                foreach ($topFiles as $file) {
                    $this->info("  - {$file->name}: {$file->versions_count} versions");
                }
            }
        }
    }
    
    /**
     * Cleanup old versions
     */
    private function cleanupVersions($fileId, $keepVersions, $olderThan, $dryRun)
    {
        $this->info('Cleaning up file versions...');
        
        $query = FileVersion::query();
        
        if ($fileId) {
            $query->where('file_id', $fileId);
        }
        
        // Find versions to delete based on keep-versions policy
        $filesToCleanup = File::whereHas('versions', function($q) use ($keepVersions) {
            $q->havingRaw('COUNT(*) > ?', [$keepVersions]);
        })->get();
        
        $deletedCount = 0;
        $freedSpace = 0;
        
        foreach ($filesToCleanup as $file) {
            $versionsToDelete = $file->versions()
                ->where('is_current', false)
                ->orderBy('created_at', 'desc')
                ->skip($keepVersions)
                ->get();
                
            foreach ($versionsToDelete as $version) {
                if ($dryRun) {
                    $this->info("Would delete version {$version->version_number} of {$file->name}");
                } else {
                    try {
                        // Delete physical file
                        if (Storage::disk('local')->exists($version->file_path)) {
                            Storage::disk('local')->delete($version->file_path);
                        }
                        
                        $freedSpace += $version->file_size;
                        $version->delete();
                        $deletedCount++;
                        
                        $this->info("Deleted version {$version->version_number} of {$file->name}");
                    } catch (\Exception $e) {
                        $this->error("Failed to delete version {$version->id}: " . $e->getMessage());
                    }
                }
            }
        }
        
        // Find versions older than specified days
        $oldVersions = $query->where('created_at', '<', now()->subDays($olderThan))
            ->where('is_current', false)
            ->get();
            
        foreach ($oldVersions as $version) {
            if ($dryRun) {
                $this->info("Would delete old version {$version->version_number} of file {$version->file_id}");
            } else {
                try {
                    if (Storage::disk('local')->exists($version->file_path)) {
                        Storage::disk('local')->delete($version->file_path);
                    }
                    
                    $freedSpace += $version->file_size;
                    $version->delete();
                    $deletedCount++;
                    
                    $this->info("Deleted old version {$version->version_number} of file {$version->file_id}");
                } catch (\Exception $e) {
                    $this->error("Failed to delete old version {$version->id}: " . $e->getMessage());
                }
            }
        }
        
        if ($dryRun) {
            $this->info("Dry run completed. Would delete {$deletedCount} versions.");
        } else {
            $this->info("Cleanup completed. Deleted {$deletedCount} versions.");
            $this->info("Freed space: " . number_format($freedSpace / 1024 / 1024, 2) . " MB");
        }
    }
    
    /**
     * Verify version integrity
     */
    private function verifyVersions($fileId = null)
    {
        $this->info('Verifying version integrity...');
        
        $query = FileVersion::query();
        
        if ($fileId) {
            $query->where('file_id', $fileId);
        }
        
        $versions = $query->get();
        $totalVersions = $versions->count();
        $validVersions = 0;
        $invalidVersions = 0;
        $missingFiles = 0;
        
        $progressBar = $this->output->createProgressBar($totalVersions);
        $progressBar->start();
        
        foreach ($versions as $version) {
            $fileExists = Storage::disk('local')->exists($version->file_path);
            
            if ($fileExists) {
                // Verify file hash
                $currentHash = hash_file('sha256', Storage::disk('local')->path($version->file_path));
                if ($currentHash === $version->file_hash) {
                    $validVersions++;
                } else {
                    $invalidVersions++;
                    $this->newLine();
                    $this->warn("Hash mismatch for version {$version->id}");
                }
            } else {
                $missingFiles++;
                $this->newLine();
                $this->error("Missing file for version {$version->id}: {$version->file_path}");
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $this->info("Verification completed:");
        $this->info("  Total versions: {$totalVersions}");
        $this->info("  Valid versions: {$validVersions}");
        $this->info("  Invalid versions: {$invalidVersions}");
        $this->info("  Missing files: {$missingFiles}");
        
        if ($invalidVersions > 0 || $missingFiles > 0) {
            $this->warn("Issues found! Consider running cleanup or manual intervention.");
        }
    }
    
    /**
     * Find orphaned version files
     */
    private function findOrphanedVersions($dryRun)
    {
        $this->info('Finding orphaned version files...');
        
        // Get all version file paths from database
        $dbPaths = FileVersion::pluck('file_path')->toArray();
        
        // Scan storage for version files
        $storagePaths = [];
        $this->scanVersionDirectory('files', $storagePaths);
        
        $orphanedFiles = array_diff($storagePaths, $dbPaths);
        
        if (empty($orphanedFiles)) {
            $this->info("No orphaned version files found.");
            return;
        }
        
        $this->warn("Found " . count($orphanedFiles) . " orphaned version files:");
        
        $totalSize = 0;
        foreach ($orphanedFiles as $filePath) {
            $size = Storage::disk('local')->size($filePath);
            $totalSize += $size;
            
            if ($dryRun) {
                $this->info("Would delete: {$filePath} (" . number_format($size / 1024 / 1024, 2) . " MB)");
            } else {
                try {
                    Storage::disk('local')->delete($filePath);
                    $this->info("Deleted: {$filePath}");
                } catch (\Exception $e) {
                    $this->error("Failed to delete {$filePath}: " . $e->getMessage());
                }
            }
        }
        
        if ($dryRun) {
            $this->info("Would free " . number_format($totalSize / 1024 / 1024, 2) . " MB of space");
        } else {
            $this->info("Freed " . number_format($totalSize / 1024 / 1024, 2) . " MB of space");
        }
    }
    
    /**
     * Recursively scan version directory
     */
    private function scanVersionDirectory($directory, &$files)
    {
        $contents = Storage::disk('local')->listContents($directory);
        
        foreach ($contents as $item) {
            if ($item['type'] === 'file') {
                $files[] = $item['path'];
            } elseif ($item['type'] === 'dir') {
                $this->scanVersionDirectory($item['path'], $files);
            }
        }
    }
}
