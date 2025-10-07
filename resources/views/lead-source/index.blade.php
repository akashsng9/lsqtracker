@extends('layouts.app')

@section('title', 'Lead Sources')

@push('styles')
<style>
    .nav-tabs .nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .nav-tabs .badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
    }

    .validation-message {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
    }

    .validation-message .fa {
        margin-right: 0.25rem;
    }

    .invalid-feedback {
        display: block;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        padding-right: calc(1.5em + 0.75rem);
    }

    .alert-dismissible .close {
        position: absolute;
        top: 0;
        right: 0;
        padding: 0.75rem 1.25rem;
        color: inherit;
        background: transparent;
        border: 0;
        font-size: 1.5rem;
        line-height: 1;
        opacity: 0.5;
    }

    #formErrorContainer {
        margin-bottom: 1rem;
    }

    .alert {
        position: relative;
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .alert-dismissible {
        padding-right: 4rem;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-body">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row mb-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">Lead Sources</h2>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('lead-sources.import.form') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-file-import mr-1"></i> Import CSV
                        </a>
                        <button type="button" class="btn btn-aaft" data-toggle="modal" data-target="#addSourceModal">
                            <i class="fa fa-plus mr-1"></i> Add Lead Source
                        </button>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3" id="leadSourceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}"
                            href="{{ route('lead-sources.index', ['tab' => 'all'] + request()->except('tab')) }}">
                            All <span class="badge bg-primary text-white">{{ $counts['all'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'organic' ? 'active' : '' }}"
                            href="{{ route('lead-sources.index', ['tab' => 'organic'] + request()->except('tab')) }}">
                            Organic <span class="badge bg-success text-white">{{ $counts['organic'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'paid' ? 'active' : '' }}"
                            href="{{ route('lead-sources.index', ['tab' => 'paid'] + request()->except('tab')) }}">
                            Paid <span class="badge bg-primary text-white">{{ $counts['paid'] }}</span>
                        </a>
                    </li>
                </ul>

                <div id="formErrorContainer" class="mb-3 d-none"></div>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="sourcesTable">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Lead Source</th>
                        <th>Source Type</th>
                        <th>Source/Campaign</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leadSources as $index => $source)
                    <tr id="source-{{ $source->id }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $source->leadSource }}</td>
                        <td>
                            <span class="badge {{ $source->sourceType === 'Paid' ? 'badge-warning' : 'badge-success' }}">
                                {{ $source->sourceType }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $source->type == '1' ? 'badge-success' : 'badge-warning' }}">
                                {{ $source->type == '1' ? 'Source' : 'Campaign' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('lead-sources.edit', $source->id) }}" class="btn btn-sm btn-aaft mr-2">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <!-- <button class="btn btn-sm btn-danger delete-source"
                                data-id="{{ $source->id }}"
                                data-name="{{ $source->leadSource }}">
                                <i class="fa fa-trash"></i> Delete
                            </button> -->
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">No lead sources found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Source Modal -->
<div class="modal fade" id="addSourceModal" tabindex="-1" role="dialog" aria-labelledby="addSourceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Lead Source</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="addSourceForm" action="{{ route('lead-sources.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="sourceType">Source Type</label>
                        <select class="form-control" id="sourceType" name="sourceType" required>
                            <option value="">Select Source Type</option>
                            <option value="Paid">Paid</option>
                            <option value="Organic">Organic</option>
                        </select>
                    </div>

                    <h6>Select any one source:</h6>
                    <div class="form-group">
                        <input type="radio" name="sourceOption" id="sourceCheck" value="leadSource" checked>
                        <label for="sourceCheck">Lead Source</label>
                        <input type="radio" name="sourceOption" id="sourceCampaignCheck" value="sourceCampaign">
                        <label for="sourceCampaignCheck">Source Campaign</label>
                    </div>

                    <div class="form-group">
                        <label for="leadSource">Lead Source Name</label>
                        @php
                        $currentSource = old('leadSource', '');
                        $options = collect($sources)->pluck('Source')->filter()->unique()->sort()->values();
                        @endphp
                        <select name="leadSource" id="leadSource" class="form-control select2-source">
                            <option value="">Select or type lead source</option>
                            @foreach($options as $source)
                            <option value="{{ $source }}" {{ (string)$currentSource === (string)$source ? 'selected' : '' }}>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center font-weight-bold text-danger">OR</div>

                    <div class="form-group">
                        <label for="sourceCampaign">Source Campaign</label>
                        @php
                        $currentCampaign = old('sourceCampaign', '');
                        $options = collect($sourceCampaign)->pluck('SourceCampaign')->filter()->unique()->sort()->values();
                        @endphp
                        <select name="sourceCampaign" id="sourceCampaign" class="form-control select2-source" disabled>
                            <option value="">Select or type source campaign</option>
                            @foreach($options as $campaign)
                            <option value="{{ $campaign }}" {{ (string)$currentCampaign === (string)$campaign ? 'selected' : '' }}>{{ $campaign }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-reset" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-aaft">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
   $(document).ready(function() {
    let errorContainer = $('#formErrorContainer');

    function handleSourceTypeChange() {
        const selectedValue = $('input[name="sourceOption"]:checked').val();
        if (selectedValue === 'leadSource') {
            $('#leadSource').prop('disabled', false).prop('required', true);
            $('#sourceCampaign').prop('disabled', true).prop('required', false).val(null).trigger('change');
        } else {
            $('#leadSource').prop('disabled', true).prop('required', false).val(null).trigger('change');
            $('#sourceCampaign').prop('disabled', false).prop('required', true);
        }
    }

    $('input[name="sourceOption"]').on('change', handleSourceTypeChange);
    handleSourceTypeChange();

    $('.select2-source').select2({
        width: '100%',
        placeholder: 'Select or type lead source',
        dropdownParent: $('#addSourceModal'),
        tags: true
    });

    // Reset modal form every time it's opened
    $('#addSourceModal').on('show.bs.modal', function() {
        let form = $('#addSourceForm');
        form[0].reset(); // reset inputs
        $('.select2-source').val(null).trigger('change'); // reset Select2
        $('input[name="sourceOption"][value="leadSource"]').prop('checked', true); // default radio
        handleSourceTypeChange(); // reset field enable/disable
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        errorContainer.addClass('d-none').empty(); // clear previous messages
    });

    $('#addSourceForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let formData = new FormData(this);
        let submitBtn = form.find('button[type="submit"]');
        let originalBtnText = submitBtn.html();

        const optionType = $('input[name="sourceOption"]:checked').val();
        formData.append('optionType', optionType);

        if (optionType === 'leadSource') formData.delete('sourceCampaign');
        else formData.delete('leadSource');

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        errorContainer.addClass('d-none').empty();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').remove();

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            complete: function() {
                $('#addSourceModal').modal('hide'); // always hide modal
                submitBtn.prop('disabled', false).html(originalBtnText);
            },
            success: function(response) {
                form[0].reset();
                $('.select2-source').val(null).trigger('change');
                $('input[name="sourceOption"][value="leadSource"]').prop('checked', true);
                handleSourceTypeChange();

                errorContainer.html(`<div class="alert alert-success">${response.message || 'Lead source added successfully!'}</div>`).removeClass('d-none');
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function(xhr) {
                let res = xhr.responseJSON || {};
                let message = res.message || 'Please fix the form errors.';

                errorContainer.html(`<div class="alert alert-danger">${message}</div>`).removeClass('d-none');

                if (res.errors) {
                    $.each(res.errors, function(field, errors) {
                        let input = $('[name="' + field + '"]');
                        if (!input.hasClass('select2-hidden-accessible')) {
                            input.addClass('is-invalid');
                            input.after('<div class="invalid-feedback">' + errors.join('<br>') + '</div>');
                        } else {
                            let select2Container = input.next('.select2-container');
                            select2Container.addClass('is-invalid');
                            if (!select2Container.next('.invalid-feedback').length) {
                                select2Container.after('<div class="invalid-feedback">' + errors.join('<br>') + '</div>');
                            }
                        }
                    });
                }
            }
        });
    });
});

</script>
@endpush

@endsection