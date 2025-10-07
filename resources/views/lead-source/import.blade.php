@extends('layouts.app')

@section('title', 'Import Lead Sources')

@push('styles')
<style>
    .file-input-container {
        position: relative;
        overflow: hidden;
        display: inline-block;
    }
    .file-input {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    .file-input-label {
        display: inline-block;
        padding: 8px 16px;
        background-color: #4e73df;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .file-input-label:hover {
        background-color: #2e59d9;
    }
    .file-name {
        margin-left: 10px;
        font-style: italic;
    }
    .progress {
        display: none;
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-file-import me-2"></i>Import Lead Sources</h4>
                        <a href="{{ route('lead-sources.index') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('status'))
                        <div class="alert alert-{{ session('status') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show" role="alert">
                            <i class="fas {{ session('status') === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' }} me-2"></i>
                            {{ session('message', session('status')) }}
                            
                            @if(session('filename'))
                                <div class="mt-2">
                                    <strong>File:</strong> {{ session('filename') }}
                            @endif
                            
                            @if(session('processing') === true)
                                <div class="mt-3">
                                    <div class="progress">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                             role="progressbar" 
                                             style="width: 100%"
                                             aria-valuenow="100" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            Processing...
                                        </div>
                                    </div>
                                    <p class="small mt-2 mb-0">
                                        <i class="fas fa-info-circle me-1"></i>
                                        This is a large file. Processing is running in the background. You can close this page.
                                    </p>
                                </div>
                            @endif
                                </div>
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h5 class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i> Import Instructions
                        </h5>
                        <p>Please upload a CSV file with the following columns:</p>
                        <ul class="mb-3">
                            <li><strong>Lead Source</strong> (required) - The name of the lead source</li>
                            <li><strong>Source Type</strong> (required) - Either 'Paid' or 'Organic'</li>
                            <li><strong>Type</strong> (optional) - Defaults to 'General' if not provided</li>
                            <li><strong>Status</strong> (optional) - Defaults to 'Active' if not provided</li>
                        </ul>
                        <a href="{{ asset('sample_lead_sources.csv') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Download Sample CSV
                        </a>
                    </div>

                    <form id="importForm" action="{{ route('lead-sources.import') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        @csrf
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Upload CSV File</h5>
                                
                                <div class="file-input-container mb-3">
                                    <label for="csv_file" class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt me-2"></i>Choose File
                                    </label>
                                    <span id="file-name" class="file-name">No file chosen</span>
                                    <input 
                                        type="file" 
                                        class="file-input @error('csv_file') is-invalid @enderror" 
                                        id="csv_file" 
                                        name="csv_file" 
                                        accept=".csv,.txt" 
                                        required
                                    >
                                    @error('csv_file')
                                        <div class="invalid-feedback d-block">
                                            <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text">Maximum file size: 1GB. Allowed extensions: .csv, .txt</div>
                                </div>

                                <div class="progress" id="upload-progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" 
                                         style="width: 0%" 
                                         aria-valuenow="0" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        0%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('lead-sources.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-button">
                                <i class="fas fa-upload me-1"></i> Start Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('csv_file');
        const fileNameSpan = document.getElementById('file-name');
        const progressBar = document.querySelector('.progress-bar');
        const progressContainer = document.getElementById('upload-progress');
        const submitButton = document.getElementById('submit-button');
        const importForm = document.getElementById('importForm');

        // Update file name display
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileSize = (file.size / (1024 * 1024)).toFixed(2); // Convert to MB
                fileNameSpan.textContent = `${file.name} (${fileSize} MB)`;
                
                // Show file size warning for large files
                if (file.size > 50 * 1024 * 1024) { // 50MB
                    alert('Warning: You are uploading a large file. The import may take several minutes to complete.');
                }
            } else {
                fileNameSpan.textContent = 'No file chosen';
            }
        });

        // Form submission with progress
        importForm.addEventListener('submit', function(e) {
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return false;
            }

            // Show progress bar
            progressContainer.style.display = 'block';
            
            // Disable submit button
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing...';
            
            // Simulate progress (actual progress would need XHR for real progress)
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 5;
                if (progress > 90) {
                    clearInterval(progressInterval);
                    return;
                }
                updateProgress(progress);
            }, 200);
        });

        function updateProgress(percent) {
            const progressBar = document.querySelector('.progress-bar');
            progressBar.style.width = percent + '%';
            progressBar.setAttribute('aria-valuenow', percent);
            progressBar.textContent = percent + '%';
        }
    });
</script>
@endpush

<style>
    .card {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .card-header {
        background-color: #4e73df;
        color: white;
        border-bottom: none;
        padding: 1.25rem 1.5rem;
    }
    
    .card-header h4 {
        font-weight: 600;
        margin: 0;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .alert {
        border-left: 4px solid;
        border-radius: 0.35rem;
    }
    
    .alert-info {
        border-left-color: #36b9cc;
        background-color: #f8f9fc;
    }
    
    .alert-success {
        border-left-color: #1cc88a;
        background-color: #f0fdf4;
    }
    
    .alert-danger {
        border-left-color: #e74a3b;
        background-color: #fef2f2;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        background-color: #2e59d9;
        border-color: #2e59d9;
    }
    
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #d1d3e2;
    }
    
    .btn-outline-secondary:hover {
        background-color: #f8f9fc;
        border-color: #bac8f3;
        color: #4e73df;
    }
    
    .form-control:focus, .form-select:focus, .form-check-input:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
    }
    
    .progress {
        height: 1.5rem;
        border-radius: 0.35rem;
        margin-top: 1.5rem;
    }
    
    .progress-bar {
        background-color: #4e73df;
        font-size: 0.8rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-text {
        font-size: 0.8rem;
        color: #858796;
        margin-top: 0.5rem;
    }
    
    .file-input-container {
        margin-bottom: 1.5rem;
    }
    
    .file-input-label {
        padding: 0.5rem 1.25rem;
        border-radius: 0.35rem;
        font-weight: 500;
    }
    
    .file-name {
        margin-left: 1rem;
        color: #6c757d;
    }
    
    .card-title {
        color: #4e73df;
        font-weight: 600;
    }
</style>
@endsection
