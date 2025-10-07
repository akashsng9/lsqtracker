<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessLeadImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $chunkSize = 1000;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param int $userId
     * @return void
     */
    public function __construct($filePath, $userId)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info('Starting lead import job', [
            'file' => $this->filePath,
            'user_id' => $this->userId,
            'file_exists' => file_exists($this->filePath) ? 'yes' : 'no',
            'file_size' => file_exists($this->filePath) ? filesize($this->filePath) : 0
        ]);

        if (!file_exists($this->filePath)) {
            Log::error("File not found: " . $this->filePath);
            return;
        }

        // Check if file is readable
        if (!is_readable($this->filePath)) {
            Log::error('File is not readable', ['file' => $this->filePath]);
            return;
        }

        // Check if file is empty
        if (filesize($this->filePath) === 0) {
            Log::error('File is empty', ['file' => $this->filePath]);
            return;
        }

        $handle = @fopen($this->filePath, 'r');
        
        if ($handle === false) {
            $error = error_get_last();
            Log::error('Failed to open file', [
                'file' => $this->filePath,
                'error' => $error ? $error['message'] : 'Unknown error'
            ]);
            return;
        }

        // Read headers
        $header = fgetcsv($handle);
        
        // Log the first few lines for debugging
        $firstFewLines = [];
        $lineCount = 0;
        $maxLines = 5;
        
        if ($header === false) {
            // Try to read the file content to understand what's wrong
            $fileContent = file_get_contents($this->filePath);
            $firstFewLines = array_slice(explode("\n", $fileContent), 0, $maxLines);
            
            Log::error("Invalid CSV format in file: " . $this->filePath, [
                'first_few_lines' => $firstFewLines,
                'file_size' => filesize($this->filePath),
                'file_content_type' => mime_content_type($this->filePath)
            ]);
            
            fclose($handle);
            return;
        }
        
        // Log the header for debugging
        Log::info('CSV Header', ['header' => $header]);

        $header = array_map('trim', $header);
        
        // Define required fields
        $requiredFields = ['ProspectID', 'FirstName', 'LastName', 'EmailAddress'];
        $missingFields = array_diff($requiredFields, $header);
        
        if (count($missingFields) > 0) {
            fclose($handle);
            Log::error("Missing required columns: " . implode(', ', $missingFields));
            return;
        }

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];
        $currentRow = 1; // Start from 1 to account for header
        $batch = [];

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $currentRow++;
                
                // Skip empty rows
                if (count(array_filter($row)) === 0) {
                    $skippedCount++;
                    continue;
                }

                // Combine with headers and trim values
                $rowData = array_combine($header, array_map('trim', $row));
                
                // Validate required fields
                $missingData = false;
                foreach ($requiredFields as $field) {
                    if (empty($rowData[$field])) {
                        $errors[] = "Row $currentRow: Missing required field - $field";
                        $skippedCount++;
                        $missingData = true;
                        break;
                    }
                }
                
                if ($missingData) {
                    continue;
                }

                // Validate email format
                if (!filter_var($rowData['EmailAddress'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row $currentRow: Invalid email format - " . $rowData['EmailAddress'];
                    $skippedCount++;
                    continue;
                }

                // Map all fields from the CSV to the database columns
                $mappedData = [];
                foreach ($header as $index => $column) {
                    if (isset($row[$index])) {
                        $mappedData[$column] = $row[$index];
                    }
                }

                // Set default values
                $mappedData['Score'] = is_numeric($mappedData['Score'] ?? 0) ? (int)$mappedData['Score'] : 0;
                $mappedData['EngagementScore'] = is_numeric($mappedData['EngagementScore'] ?? 0) ? (int)$mappedData['EngagementScore'] : 0;
                $mappedData['ProspectStage'] = $mappedData['ProspectStage'] ?? 'New';
                $mappedData['created_at'] = now();
                $mappedData['updated_at'] = now();
                $mappedData['imported_by'] = $this->userId;

                $batch[] = $mappedData;

                // Process in chunks
                if (count($batch) >= $this->chunkSize) {
                    $this->processBatch($batch);
                    $importedCount += count($batch);
                    $batch = [];
                    
                    // To prevent memory issues, we'll clear the query log
                    DB::connection()->unsetEventDispatcher();
                    DB::connection()->disableQueryLog();
                }
            }

            // Process any remaining items in the batch
            if (!empty($batch)) {
                $this->processBatch($batch);
                $importedCount += count($batch);
            }

            DB::commit();

            // Log the results
            Log::info("Import completed. Imported: $importedCount, Skipped: $skippedCount");
            
            // Optionally, you could send a notification to the user here
            // Notification::send(User::find($this->userId), new ImportCompleted($importedCount, $skippedCount));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during import: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
        } finally {
            fclose($handle);
            
            // Clean up the temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
        }
    }

    /**
     * Process a batch of records.
     *
     * @param array $batch
     * @return void
     */
    protected function processBatch(array $batch)
    {
        if (empty($batch)) {
            return;
        }

        try {
            foreach (array_chunk($batch, 100) as $chunk) {
                foreach ($chunk as $record) {
                    $prospectId = $record['ProspectID'];
                    unset($record['ProspectID']);
                    
                    Lead::updateOrCreate(
                        ['ProspectID' => $prospectId],
                        $record
                    );
                }
                
                // Clear the query log to prevent memory issues
                DB::connection()->unsetEventDispatcher();
                DB::connection()->disableQueryLog();
                
                // Free up memory
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing batch: ' . $e->getMessage());
            throw $e;
        }
    }
}
