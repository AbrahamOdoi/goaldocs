<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\DocumentPreviewService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateFilePreviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:generate-previews {--limit=50 : Number of files to process} {--force : Force regeneration of existing previews} {--type= : Specific file type to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate previews for files that support preview functionality';

    /**
     * Execute the console command.
     */
    public function handle(DocumentPreviewService $previewService)
    {
        $this->info('Starting file preview generation...');
        
        $limit = $this->option('limit');
        $force = $this->option('force');
        $type = $this->option('type');
        
        // Get files that need preview generation
        $query = File::query();
        
        if (!$force) {
            $query->whereNull('search_metadata->preview');
        }
        
        if ($type) {
            $query->where('mime_type', 'like', "%{$type}%");
        }
        
        $files = $query->limit($limit)->get();
        
        if ($files->isEmpty()) {
            $this->info('No files found that need preview generation.');
            return 0;
        }
        
        $this->info("Found {$files->count()} files to process for preview generation.");
        
        $progressBar = $this->output->createProgressBar($files->count());
        $progressBar->start();
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        $previewTypes = [];
        
        foreach ($files as $file) {
            try {
                if ($previewService->supportsPreview($file)) {
                    $preview = $previewService->generatePreview($file);
                    
                    // Update file with preview metadata
                    $searchMetadata = $file->search_metadata ?? [];
                    $searchMetadata['preview'] = $preview;
                    
                    $file->update([
                        'search_metadata' => $searchMetadata,
                        'indexed_at' => now(),
                    ]);
                    
                    $successCount++;
                    $previewTypes[$preview['type']] = ($previewTypes[$preview['type']] ?? 0) + 1;
                } else {
                    $this->warn("File {$file->id} ({$file->mime_type}) does not support preview generation.");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error generating preview for file {$file->id}: {$e->getMessage()}";
                Log::error("Error generating preview for file {$file->id}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $this->info("Preview generation completed!");
        $this->info("Successfully generated: {$successCount} previews");
        $this->info("Failed to generate: {$errorCount} previews");
        
        if (!empty($previewTypes)) {
            $this->info("Preview types generated:");
            foreach ($previewTypes as $type => $count) {
                $this->info("  - {$type}: {$count}");
            }
        }
        
        if (!empty($errors)) {
            $this->warn("Errors encountered:");
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->error($error);
            }
            
            if (count($errors) > 10) {
                $this->warn("... and " . (count($errors) - 10) . " more errors.");
            }
        }
        
        // Show statistics
        $totalFiles = File::count();
        $filesWithPreview = File::whereNotNull('search_metadata->preview')->count();
        $filesWithoutPreview = File::whereNull('search_metadata->preview')->count();
        
        $this->newLine();
        $this->info("Preview Generation Statistics:");
        $this->info("Total files: {$totalFiles}");
        $this->info("Files with preview: {$filesWithPreview}");
        $this->info("Files without preview: {$filesWithoutPreview}");
        
        if ($filesWithoutPreview > 0) {
            $this->warn("Run this command again to generate previews for remaining files.");
        }
        
        return 0;
    }
}
