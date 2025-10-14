@extends('layouts.app')
@section('title', 'Config Lead')
@section('content')

<div class="card">
    <div class="card-body">
        <h2>Mumbai Team</h2>
        <div class="card mt-3">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('team.mumbai.filter') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <label for="course_name">Course Name</label>
                            <select name="course_name[]" id="course_name" class="form-control select2-multiple" multiple data-placeholder="Select Course Name">
                                @foreach($courses as $course)
                                <option value="{{ $course->courseName }}">{{ $course->courseName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="tl_name">TL Name</label>
                            <select name="tl_name[]" id="tl_name" class="form-control select2-multiple" multiple data-placeholder="Select TL Name(s)">
                                @foreach($teamLead as $tl)
                                <option value="{{ $tl->id }}">{{ $tl->tl_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="lc_name">LC Name</label>
                            <select name="lc_name[]" id="lc_name" class="form-control select2-multiple" multiple data-placeholder="Select LC Name(s)">
                                @foreach($lcs as $lc)
                                <option value="{{ $lc->id }}">{{ $lc->lcName }} {{ $lc->location ? '(' . $lc->location . ')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="lead_type">Lead Type</label>
                            <select name="lead_type" id="lead_type" class="form-control">
                                @foreach($leadType as $type)
                                <option value="{{ $type->sourceType }}">{{ $type->sourceType }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-aaft mt-3">Filter</button>
            </div>
        </div>

        <!-- table -->
        <div class="table-responsive mt-2">
            <table class="table table-bordered table-striped table-hover" id="mumbaiTable" style="width:100%">
                <thead>
                    <tr>
                        <th>Sl.</th>
                        <th>ProspectID</th>
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody id="filteredData">
                    @forelse($leads as $index => $lead)
                    <tr>
                        <td>{{ ($leads->currentPage() - 1) * $leads->perPage() + $loop->iteration }}</td>
                        <td>{{ $lead->ProspectID ?? 'N/A' }}</td>
                        <td>{{ trim(($lead->FirstName ?? '') . ' ' . ($lead->LastName ?? '')) ?: 'N/A' }}</td>
                        <td>{{ $lead->Phone ?? 'N/A' }}</td>
                        <td>{{ $lead->EmailAddress ?? 'N/A' }}</td>
                        <td>{{ $lead->City ?? 'N/A' }}</td>
                        <td>{{ $lead->lead_source_type ?? ($lead->Source ?? 'N/A') }}</td>
                        @php
                            $status = $lead->Status ?? 'Inactive';
                            $statusClass = 'bg-secondary';
                            if ($status === 'Active') $statusClass = 'bg-success';
                            elseif ($status === 'Pending') $statusClass = 'bg-warning';
                            elseif ($status === 'Inactive') $statusClass = 'bg-danger';
                        @endphp
                        <td><span class="badge {{ $statusClass }}">{{ $status }}</span></td>
                        <td>{{ $lead->CreatedOn ? \Carbon\Carbon::parse($lead->CreatedOn)->format('d-m-Y H:i') : 'N/A' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No data available. Apply filters to see results.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <!-- Pagination -->
            @if(method_exists($leads, 'links'))
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} entries
                </div>
                <div class="pagination-container">
                    {{ $leads->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection
@section('scripts')
<script>
$(document).ready(function() {
    // Handle form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#filteredData').html('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        
        // Get form data
        var formData = $(this).serialize();
        
        // Get current page from pagination
        var page = $('.pagination .page-item.active .page-link').text();
        if (page) {
            formData += '&page=' + page;
        }
        
        // Send AJAX request
        $.ajax({
            url: $(this).attr('action'),
            type: 'GET',
            data: formData,
            success: function(response) {
                if(response.success && response.data && response.data.length > 0) {
                    var html = '';
                    var start = (response.pagination.current_page - 1) * response.pagination.per_page;
                    
                    $.each(response.data, function(index, lead) {
                        // Format date
                        var createdOn = lead.CreatedOn ? new Date(lead.CreatedOn).toLocaleString() : 'N/A';
                        
                        // Determine status badge class
                        var status = lead.Status || 'Inactive';
                        var statusClass = 'bg-secondary';
                        if(status === 'Active') statusClass = 'bg-success';
                        else if(status === 'Pending') statusClass = 'bg-warning';
                        else if(status === 'Inactive') statusClass = 'bg-danger';
                        
                        html += `
                        <tr>
                            <td>${start + index + 1}</td>
                            <td>${lead.ProspectID || 'N/A'}</td>
                            <td>${(lead.FirstName || '') + ' ' + (lead.LastName || '') || 'N/A'}</td>
                            <td>${lead.Phone || 'N/A'}</td>
                            <td>${lead.EmailAddress || 'N/A'}</td>
                            <td>${lead.City || 'N/A'}</td>
                            <td>${lead.lead_source_type || (lead.Source || 'N/A')}</td>
                            <td><span class="badge ${statusClass}">${status}</span></td>
                            <td>${createdOn}</td>
                        </tr>`;
                    });
                    
                    $('#filteredData').html(html);
                    
                    // Update pagination
                    updatePagination(response.pagination);
                } else {
                    $('#filteredData').html('<tr><td colspan="9" class="text-center">No data found matching your criteria.</td></tr>');
                    $('.pagination-container').html('');
                }
            },
            error: function(xhr) {
                console.error(xhr);
                $('#filteredData').html('<tr><td colspan="9" class="text-center text-danger">Error loading data. Please try again.</td></tr>');
                $('.pagination-container').html('');
            }
        });
    });
    
    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var page = url.split('page=')[1];
        
        // Update form with page number and submit
        var form = $('#filterForm');
        form.find('input[name="page"]').remove();
        form.append('<input type="hidden" name="page" value="' + page + '">');
        form.submit();
    });
    
    // Function to update pagination
    function updatePagination(pagination) {
        if (!pagination) {
            $('.pagination-container').html('');
            return;
        }
        
        var html = `
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-end">
                <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="${pagination.prev_page_url || '#'}" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`;
        
        for (var i = 1; i <= pagination.last_page; i++) {
            html += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link" href="${pagination.path + '?page=' + i}">${i}</a>
                </li>`;
        }
        
        html += `
                <li class="page-item ${pagination.current_page === pagination.last_page ? 'disabled' : ''}">
                    <a class="page-link" href="${pagination.next_page_url || '#'}" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="text-muted mt-2">
            Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total} entries
        </div>`;
        
        $('.pagination-container').html(html);
    }
    
    // Initialize the form on page load
    $('#filterForm').trigger('submit');
    
    // Close the document.ready function
});
</script>

@endsection