<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Services\DocumentProcessingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessFilesForSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:process-search {--limit=100 : Number of files to process} {--force : Force reprocessing of already indexed files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process files for full-text search indexing';

    /**
     * Execute the console command.
     */
    public function handle(DocumentProcessingService $documentProcessingService)
    {
        $this->info('Starting file processing for search indexing...');
        
        $limit = $this->option('limit');
        $force = $this->option('force');
        
        // Get files that need processing
        $query = File::query();
        
        if (!$force) {
            $query->whereNull('indexed_at');
        }
        
        $files = $query->limit($limit)->get();
        
        if ($files->isEmpty()) {
            $this->info('No files found that need processing.');
            return 0;
        }
        
        $this->info("Found {$files->count()} files to process.");
        
        $progressBar = $this->output->createProgressBar($files->count());
        $progressBar->start();
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($files as $file) {
            try {
                if ($documentProcessingService->processFileForSearch($file)) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Failed to process file {$file->id}: {$file->name}";
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Error processing file {$file->id}: {$e->getMessage()}";
                Log::error("Error processing file {$file->id} for search: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine();
        
        $this->info("Processing completed!");
        $this->info("Successfully processed: {$successCount} files");
        $this->info("Failed to process: {$errorCount} files");
        
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
        $indexedFiles = File::whereNotNull('indexed_at')->count();
        $unindexedFiles = File::whereNull('indexed_at')->count();
        
        $this->newLine();
        $this->info("Search Indexing Statistics:");
        $this->info("Total files: {$totalFiles}");
        $this->info("Indexed files: {$indexedFiles}");
        $this->info("Unindexed files: {$unindexedFiles}");
        
        if ($unindexedFiles > 0) {
            $this->warn("Run this command again to process remaining files.");
        }
        
        return 0;
    }
}
