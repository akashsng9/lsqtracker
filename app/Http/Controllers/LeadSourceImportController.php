<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessLeadImport;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeadSourceImportController extends Controller
{
    /**
     * Show the form for importing lead sources.
     */
    public function create()
    {
        return view('lead-source.import');
    }

    /**
     * The number of rows to process in each chunk.
     *
     * @var int
     */
    protected $chunkSize = 500; // Reduced chunk size for better memory management

    /**
     * Process large files in background
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function processLargeFile($file)
    {
        try {
            // Generate a unique filename
            $filename = 'import_' . time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $filename);
            
            // Dispatch job to process the file in background
            ProcessLeadImport::dispatch(storage_path('app/' . $path), auth()->id())
                ->onQueue('imports');
                
            return back()->with([
                'status' => 'success',
                'message' => 'Your file is being processed. You will be notified when it\'s complete.',
                'filename' => $file->getClientOriginalName(),
                'processing' => true
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing large file: ' . $e->getMessage());
            return back()->with('error', 'Error processing file: ' . $e->getMessage());
        }
    }

    /**
     * Import lead sources from CSV file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // First, validate the request
        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:1048576', // 1GB max
        ]);

        try {
            $file = $request->file('csv_file');
            
            // Check if file is valid
            if (!$file->isValid()) {
                return back()->with('error', 'The uploaded file is not valid.');
            }
            
            // Check file size (in KB)
            $fileSize = $file->getSize() / 1024; // Convert to KB
            
            // If file is larger than 500MB, process in background
            if ($fileSize > 512000) { // 500MB in KB
                return $this->processLargeFile($file);
            }
            
            // Process smaller files immediately
            $filename = $file->getClientOriginalName();
            $filesize = $file->getSize();
            
            Log::info("File upload started", [
                'filename' => $filename,
                'size' => $filesize,
                'mime' => $file->getMimeType()
            ]);
            
            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp-imports');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Generate a unique filename
            $tempFilename = 'import_' . time() . '_' . $filename;
            $tempPath = $file->storeAs('temp-imports', $tempFilename);
            
            if (!$tempPath) {
                throw new \Exception('Failed to store the uploaded file.');
            }
            
            $fullPath = storage_path('app/' . $tempPath);
            
            // Verify the file was actually written
            if (!file_exists($fullPath)) {
                throw new \Exception('Failed to verify the uploaded file.');
            }
            
            Log::info("File stored successfully", [
                'temp_path' => $tempPath,
                'full_path' => $fullPath,
                'file_size' => filesize($fullPath)
            ]);
            
            // Dispatch the import job
            dispatch(new \App\Jobs\ProcessLeadImport($fullPath, auth()->id()));
            
            return back()->with([
                'status' => 'Your file is being processed. You will be notified once the import is complete.',
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            Log::error('Error in LeadSourceImportController: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while processing your request: ' . $e->getMessage());
        }
    }
    
}
