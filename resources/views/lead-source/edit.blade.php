@extends('layouts.app')

@section('title', 'Edit Lead Source')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Edit Lead Source</h2>
                    <a href="{{ route('lead-sources.index') }}" class="btn btn-reset">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('lead-sources.update', $source->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_method" value="PUT">
                    
                    <div class="form-group">
                        <label for="sourceType">Source Type <span class="text-danger">*</span></label>
                        <select class="form-control @error('sourceType') is-invalid @enderror" 
                                id="sourceType" name="sourceType" required>
                            <option value="" {{ old('sourceType', $source->sourceType) == '' ? 'selected' : '' }}>Select Type</option>
                            <option value="Paid" {{ old('sourceType', $source->sourceType) == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Organic" {{ old('sourceType', $source->sourceType) == 'Organic' ? 'selected' : '' }}>Organic</option>
                        </select>
                        @error('sourceType')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="leadSource">Lead Source Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('leadSource') is-invalid @enderror" 
                               id="leadSource" name="leadSource" 
                               value="{{ old('leadSource', $source->leadSource) }}" required>
                        @error('leadSource')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>


                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-aaft">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('lead-sources.index') }}" class="btn btn-reset">
                            Cancel
                        </a>
                    </div>
                </form>

                <!-- <hr class="my-4">

                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <i class="fa fa-exclamation-triangle"></i> Danger Zone
                    </div>
                    <div class="card-body">
                        <p>Deleting this lead source will remove it from the system. This action cannot be undone.</p>
                        <form action="{{ route('lead-sources.destroy', $source->id) }}" method="POST" 
                              class="d-inline" onsubmit="return confirm('Are you sure you want to delete this lead source?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete Lead Source
                            </button>
                        </form>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection
