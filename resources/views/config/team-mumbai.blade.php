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
                        <th>Course_Name</th>
                        <th>TL_Name</th>
                        <th>LC_Name</th>
                        <th>Lead_Type</th>
                        <th>Total_Leads</th>
                        <th>Total_Enrollment</th>
                    </tr>
                </thead>
                <tbody id="filteredData">
                    @forelse($leads as $index => $lead)
                    <tr>
                        <td>{{ ($leads->currentPage() - 1) * $leads->perPage() + $loop->iteration }}</td>
                        <td>{{ $lead->courseName ?? 'N/A' }}</td>
                        <td>{{ $lead->tl_name ?? 'N/A' }}</td>
                        <td>{{ $lead->lcName ?? 'N/A' }}</td>
                        <td>{{ $lead->lead_source_type ?? ($lead->Source ?? 'N/A') }}</td>
                        <td>{{ $lead->totalLeads ?? 'N/A' }}</td>
                        <td>{{ $lead->totalEnrollment ?? 'N/A' }}</td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No data available. Apply filters to see results.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
          
        </div>

    </div>
</div>

@endsection
@section('scripts')
<script>
$(document).ready(function() {
    // Initialize select2
    $('.select2-multiple').select2({
        width: '100%',
        placeholder: $(this).data('placeholder')
    });

    // Handle form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#filteredData').html('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        
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
                    
                    $.each(response.data, function(index, item) {
                        html += `
                        <tr>
                            <td>${start + index + 1}</td>
                            <td>${item.courseName || 'N/A'}</td>
                            <td>${item.tl_name || 'N/A'}</td>
                            <td>${item.lcName || 'N/A'}</td>
                            <td>${item.lead_source_type || 'N/A'}</td>
                            <td>${item.totalLeads || '0'}</td>
                            <td>${item.totalEnrollment || '0'}</td>
                        </tr>`;
                    });
                    
                    $('#filteredData').html(html);
                    
                    // Update pagination
                    updatePagination(response.pagination);
                } else {
                    $('#filteredData').html('<tr><td colspan="7" class="text-center">No data found matching your criteria.</td></tr>');
                    $('.pagination-container').html('');
                }
            },
            error: function(xhr) {
                // console.error(xhr);
                $('#filteredData').html('<tr><td colspan="7" class="text-center text-danger">Error loading data. Please try again.</td></tr>');
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
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="text-muted">
                Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total} entries
            </div>
            <nav aria-label="Page navigation">
                    <li class="page-item ${pagination.current_page === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="${pagination.prev_page_url || '#'}" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>`;
                // Only show a limited number of page links
                var startPage = Math.max(1, pagination.current_page - 2);
                var endPage = Math.min(pagination.last_page, startPage + 4);
                
                // Adjust start page if we're near the end
                if (endPage - startPage < 4 && startPage > 1) {
                    startPage = Math.max(1, endPage - 4);
                }
                
                // First page
                if (startPage > 1) {
                    html += `
                    <li class="page-item">
                        <a class="page-link" href="${pagination.path}?page=1">1</a>
                    </li>`;
                    if (startPage > 2) {
                        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }
                
                // Page numbers
                for (var i = startPage; i <= endPage; i++) {
                    html += `
                    <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="${pagination.path}?page=${i}">${i}</a>
                    </li>`;
                }
                
                // Last page
                if (endPage < pagination.last_page) {
                    if (endPage < pagination.last_page - 1) {
                        html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    html += `
                    <li class="page-item">
                        <a class="page-link" href="${pagination.path}?page=${pagination.last_page}">${pagination.last_page}</a>
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
        </div>`;
        
        $('.pagination-container').html(html);
    }
    
    // Trigger form submission on filter change
    $('.select2-multiple').on('change', function() {
        $('#filterForm').submit();
    });
    $('#filterForm').trigger('submit');
    
    // Close the document.ready function
});
</script>

@endsection