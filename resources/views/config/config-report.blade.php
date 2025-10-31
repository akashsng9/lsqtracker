@extends('layouts.app')

@section('title', 'Courses Report')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css" />
<style>
    table th {
        background-color: #f8f9fc;
    }

    .badge {
        font-size: 0.85em;
        padding: 0.5em 0.8em;
    }

    .btn-details {
        transition: all 0.3s ease;
    }

    .btn-details:hover {
        transform: translateY(-2px);
    }

    .details-row {
        background-color: #f8f9fc;
    }

    .details-content {
        padding: 20px;
    }

    .module-badge {
        margin: 2px;
        font-weight: normal;
    }

    .instructor-badge {
        margin: 2px;
        background-color: #e3f2fd;
        color: #0d6efd;
    }

    .prerequisite-badge {
        margin: 2px;
        background-color: #e8f5e9;
        color: #2e7d32;
    }

    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 0.2em solid rgba(0, 0, 0, 0.1);
        border-left-color: #0d6efd;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 0.5rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fa fa-chart-line me-2"></i> Courses Performance Dashboard
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="locationFilter">Filter by Location:</label>
                                <select id="locationFilter" class="form-control">
                                    <option value="">All Locations</option>
                                    @php
                                        $locations = \App\Models\CourseMaster::select('courseLocation')
                                            ->whereNotNull('courseLocation')
                                            ->distinct()
                                            ->orderBy('courseLocation')
                                            ->pluck('courseLocation');
                                    @endphp
                                    @foreach($locations as $location)
                                        <option value="{{ $location }}">{{ $location }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator -->
                    <div id="loadingIndicator" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">🔃</span>
                        </div>
                        <p class="mt-2">Loading data, please wait...</p>
                    </div>

                    <!-- Main Content (initially hidden) -->
                    <div id="mainContent" style="display: none;">
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body bg-primary text-white">
                                        <div class="d-flex">
                                            <div class="mr-auto my-auto">
                                                <i class="fa fa-users" style="font-size: 2rem;"></i>
                                            </div>
                                            <div class="text-right">
                                                <h6 class="card-title">Total Leads</h6>
                                                <h2 class="card-text" id="totalLeads">0</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body bg-warning text-dark">
                                        <div class="d-flex">
                                            <div class="mr-auto my-auto">
                                                <i class="fa fa-line-chart" style="font-size: 2rem;"></i>
                                            </div>
                                            <div class="text-right">
                                                <h6 class="card-title">Conversion (LSQ)</h6>
                                                <h2 class="card-text" id="totalConversion">0</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-body bg-success text-white">
                                        <div class="d-flex">
                                            <div class="mr-auto my-auto">
                                                <i class="fa fa-line-chart" style="font-size: 2rem;"></i>
                                            </div>
                                            <div class="text-right">
                                                <h6 class="card-title">Conversion (DB)</h6>
                                                <h2 class="card-text" id="totalSalesDBLeads">0</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="dateRange">Date Range</label>
                                    <input type="text" class="form-control" id="dateRange" placeholder="Select date range">
                                </div>                                
                            </div>
                            <div class="col-md-6 text-right mt-4">
                                <button id="refreshBtn" class="btn btn-primary">
                                    <i class="fa fa-sync-alt me-1"></i> Refresh
                                </button>
                            </div>
                        </div>

                        <table id="coursesTable" class="table table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Course Name</th>
                                    <th>Total Leads</th>
                                    <th>Enrollment (LSQ)</th>
                                    <th>Enrollment (DB)</th>
                                    <th>Conversion</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script>
    $(document).ready(function() {
    // Initialize DataTable first
    let dataTable = $('#coursesTable').DataTable({
            data: [],
            columns: [{
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '',
                },
                {
                    data: 'id'
                },
                {
                    data: 'name',
                    render: function(data, type, row) {
                        return `<strong>${data}</strong><div class="text-muted small">${row.details?.duration || ''}</div>`;
                    }
                },
                {
                    data: 'total_leads',
                    render: function(data) {
                        return data ? data.toLocaleString() : '0';
                    }
                },
                {
                    data: 'enrollment_lsq',
                    render: function(data) {
                        return data || '0';
                    }
                },
                {
                    data: 'enrollment_db',
                    render: function(data) {
                        return data || '0';
                    }
                },
                {
                    data: 'conversion',
                    render: function(data) {
                        const value = parseFloat(data) || 0;
                        const color = value > 5 ? 'success' : (value > 3 ? 'warning' : 'danger');
                        return `<span class="badge bg-${color}">${value}%</span>`;
                    }
                },
                {
                    data: 'details.duration',
                    defaultContent: 'N/A'
                },
                {
                    data: null,
                    orderable: false,
                    render: function() {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-info" title="View Details">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-warning" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>`;
                    }
                }
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            pageLength: 10,
            order: [
                [1, 'asc']
            ],
            responsive: true
        });

        // Initialize date range picker
        $('#dateRange').daterangepicker({
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        

        // Function to format the details for expandable rows
        function format(details) {
            if (!details) return '<div class="p-3">No details available</div>';

            // Lead Source Breakdown
            let leadSourceHtml = `
                <div class="details-content">
                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-funnel-dollar me-2"></i> Lead Source Breakdown
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Source</th>
                                                    <th>Leads</th>
                                                    <th>Enrollment (LSQ)</th>
                                                    <th>Enrollment (DB)</th>
                                                    <th>Conversion</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

            // ✅ Loop dynamically through details.leadSources
            const leadSources = details.leadSources || [];
            if(leadSources.length > 0) {
                leadSources.forEach(item => {
                    if (!item) return; // Skip null/undefined items
                    
                    const isTotalRow = item.sourceType && item.sourceType.includes('Total Leads');
                    leadSourceHtml += `
                        <tr class="${isTotalRow ? 'bg-primary text-white fw-bold' : ''}">
                            <td>${item.sourceType || 'N/A'}</td>
                            <td>${item.totalLeads || 0}</td>
                            <td>${item.enrollmentLSQ || 0}</td>
                            <td>${item.enrollmentDB || 0}</td>
                            <td>${item.conversion || 0}%</td>
                        </tr>`;
                });
    
                leadSourceHtml += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }
            else {
                leadSourceHtml += '<tr><td colspan="5" class="text-center">No lead source data available</td></tr>';
            }


            // Team Lead Performance
            // let teamLeadHtml = `
            //     <div class="row mt-4">
            //         <div class="col-12">
            //             <div class="card shadow mb-4">
            //                 <div class="card-header py-3">
            //                     <h6 class="m-0 font-weight-bold text-primary">
            //                         <i class="fas fa-user-tie me-2"></i> Team Leads Performance
            //                     </h6>
            //                 </div>
            //                 <div class="card-body">
            //                     <div class="table-responsive">
            //                         <table class="table table-bordered">
            //                             <thead class="table-light">
            //                                 <tr>
            //                                     <th>Team Lead</th>
            //                                     <th>LC</th>
            //                                     <th>Leads Assigned</th>
            //                                     <th>Enrollment (LSQ)</th>
            //                                     <th>Actual Enrollment (DB)</th>
            //                                     <th>Performance</th>
            //                                 </tr>
            //                             </thead>
            //                             <tbody>`;

            // const teamLeads = details.teamLeads || [];
            // if (teamLeads.length > 0) {
            //     teamLeads.forEach(lead => {
            //         teamLeadHtml += `
            //             <tr>
            //                 <td><strong>${lead.teamLeadName || 'N/A'}</strong></td>
            //                 <td>${lead.lcName || '-'}</td>
            //                 <td>${lead.LeadsAssigned || 0}</td>
            //                 <td>${lead.enrollmentLSQ || 0}</td>
            //                 <td>${lead.actualEnrollmentDB || 0}</td>
            //                 <td>${lead.tl_performance || 0}%</td>
            //             </tr>`;
            //     });
            //     teamLeadHtml += `
            //             <tr>
            //                 <td><strong>Total</strong></td>
            //                 <td></td>
            //                 <td>0</td>
            //                 <td>0</td>
            //                 <td>0</td>
            //                 <td>0%</td>
            //             </tr>`;
            // } else {
            //     teamLeadHtml += '<tr><td colspan="6" class="text-center">No team lead data available</td></tr>';
            // }

            // teamLeadHtml += `</tbody></table></div></div></div></div>`;

            let teamLeadHtml = `
                <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-tie me-2"></i> Team Leads Performance
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                            <tr>
                                <th>Team Lead</th>
                                <th>LC</th>
                                <th>Leads Assigned</th>
                                <th>Enrollment (LSQ)</th>
                                <th>Actual Enrollment (DB)</th>
                                <th>Performance</th>
                            </tr>
                            </thead>
                            <tbody>`;

                const teamLeads = details.teamLeads || [];
                if (teamLeads.length > 0) {
                teamLeads.forEach(tl => {
                    // Team Lead Header Row
                    teamLeadHtml += `
                    <tr class="table-primary">
                        <td colspan="6"><strong>${tl.teamLeadName || 'N/A'}</strong></td>
                    </tr>`;

                    // LC Rows under each TL
                    tl.lcs.forEach(lc => {
                    teamLeadHtml += `
                        <tr>
                        <td></td>
                        <td>${lc.lcName || '-'}</td>
                        <td>${lc.LeadsAssigned || 0}</td>
                        <td>${lc.enrollmentLSQ || 0}</td>
                        <td>${lc.actualEnrollmentDB || 0}</td>
                        <td>${lc.tl_performance || 0}%</td>
                        </tr>`;
                    });

                    // TL Total Row
                    teamLeadHtml += `
                    <tr class="table-secondary fw-bold">
                        <td>Total</td>
                        <td></td>
                        <td>${tl.totalLeadsAssigned}</td>
                        <td>${tl.totalEnrollmentLSQ}</td>
                        <td>${tl.totalEnrollmentDB}</td>
                        <td>${tl.tl_performance}%</td>
                    </tr>`;
                });
                } else {
                teamLeadHtml += `<tr><td colspan="6" class="text-center">No team lead data available</td></tr>`;
                }

                teamLeadHtml += `</tbody></table></div></div></div></div>`;


            // Conversion Analysis

            let conversionHtml = `
                 <div class="row">
                    <div class="col-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-percentage me-2"></i>Conversion Analysis
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>LC</th>
                                                <th>Total Leads</th>
                                                <th colspan='2'>Paid Leads</th>
                                                <th>Organic Leads</th>
                                                <th colspan='2'>Conversion (Paid)</th>
                                                <th>Conversion (Organic)</th>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <th></th>
                                                <th class="text-primary">Facebook</th>
                                                <th class="text-primary">Google</th>
                                                <th class="text-primary">Google</th>
                                                <th class="text-primary">Facebook</th>
                                                <th class="text-primary">Google</th>
                                                <th class="text-primary">Google</th>
                                            </tr>
                                        </thead>
                                        <tbody>
            `;

            const conversionAnalysis = details.conversionAnalysis || [];
            if (conversionAnalysis.length > 0) {
                conversionAnalysis.forEach(conversions => {
                    conversionHtml += `
                        <tr>
                            <td>${conversions.lcName || 'N/A'}</td>
                            <td>${conversions.totalLeads || 0}</td>
                            <td>${conversions.facebook_paid_leads || 0}</td>
                            <td>${conversions.google_paid_leads || 0}</td>
                            <td>${conversions.google_organic_leads || 0}</td>
                            <td>${conversions.facebook_paid_conversion || 0}%</td>
                            <td>${conversions.google_paid_conversion || 0}%</td>
                            <td>${conversions.google_organic_conversion || 0}%</td>
                        </tr>`;
                });
            } else {
                conversionHtml += '<tr><td colspan="8" class="text-center">No conversion analysis data available</td></tr>';
            }

            conversionHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Top Cities
            let citiesHtml = `
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">
                                    <i class="fas fa-map-marker-alt me-2"></i> Top Cities
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>City</th>
                                                <th>Total Leads</th>
                                                <th>Enrollments</th>
                                                <th>Conversion</th>
                                                <th>Conversion Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

            const topCities = details.topCities || [];
            if (topCities.length > 0) {
                topCities.forEach(city => {
                    citiesHtml += `
                        <tr>
                            <td>${city.city || 'N/A'}</td>
                            <td>${city.totalLeads || 0}</td>
                            <td>${city.enrollments || 0}</td>
                            <td>${city.conversion || 0}</td>
                            <td>${city.conversion_rate || 0}%</td>
                        </tr>`;
                });
            } else {
                citiesHtml += '<tr><td colspan="5" class="text-center">No city data available</td></tr>';
            }

            citiesHtml += `</tbody></table></div></div></div></div>`;

            return leadSourceHtml + teamLeadHtml + conversionHtml + citiesHtml + '</div>';
        }

        // (Removed polling function - we rely on the fetch() response to hide the loader)

        // Client-side guard to avoid duplicate/concurrent fetches
        let isFetching = false;

        // Function to fetch data and update the table
        function fetchData() {
            if (isFetching) return;
            isFetching = true;
            const dateRange = $('#dateRange').data('daterangepicker');
            const startDate = dateRange.startDate.format('YYYY-MM-DD');
            const endDate = dateRange.endDate.format('YYYY-MM-DD');
            const location = $('#locationFilter').val();

            // Show loading indicator
            $('#loadingIndicator').show();
            $('#mainContent').hide();

            // Get CSRF token from meta tag (defensive)
            const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
            const token = csrfMeta ? csrfMeta.content : null;

            // Build URL with query parameters
            let url = `{{ route("config.report") }}?mode=data&start_date=${startDate}&end_date=${endDate}`;
            
            // Add location filter if selected
            if (location) {
                url += `&location=${encodeURIComponent(location)}`;
            }

            // Make AJAX request
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Update dashboard and hide loader
                    updateDashboard(data);
                    console.log(data);
                    $('#loadingIndicator').hide();
                    $('#mainContent').show();
                })
                .catch(error => {
                    // console.error('Error:', error);
                    alert('Error loading data. Please try again.');
                    $('#loadingIndicator').hide();
                    $('#mainContent').show();
                })
                .finally(() => {
                    isFetching = false;
                });
        }

        // Function to update the dashboard with new data
        function updateDashboard(data) {
            // Update summary cards
            $('#totalLeads').text(data.totalLeadsCount?.toLocaleString() || '0');
            $('#totalConversion').text(data.totalConversion?.toLocaleString() || '0');
            $('#totalDbLeads').text(data.totalDbLeads?.toLocaleString() || '0');
            $('#totalSalesDBLeads').text(data.totalSalesDBLeads?.toLocaleString() || '0');

            // Calculate total leads from courses
            // const totalLeads = data.courses?.reduce((sum, course) => sum + (course.total_leads || 0), 0) || 0;
            // $('#totalLeads').text(totalLeads.toLocaleString());

            // Update DataTable with new data
            dataTable.clear().rows.add(data.courses || []).draw();
        }

        // Add event listener for opening and closing details
        $('#coursesTable tbody').on('click', 'td.dt-control', function() {
            const tr = $(this).closest('tr');
            const row = dataTable.row(tr);
            const icon = $(this).find('i');
            
            // If details already visible, hide them
            if (tr.hasClass('shown')) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa-minus').addClass('fa-plus');
                return;
            }

        // If details already present on the row data, show them immediately
        const existingDetails = row.data().details;
        if (existingDetails && (existingDetails.leadSources?.length || existingDetails.teamLeads?.length || existingDetails.topCities?.length)) {
            row.child(format(existingDetails)).show();
            tr.addClass('shown');
            icon.removeClass('fa-plus').addClass('fa-minus');
            return;
        }

        // Otherwise, lazy-load details for this course
        const courseName = row.data().name;
        row.child('<div class="p-3">Loading details...</div>').show();
        icon.removeClass('fa-plus').addClass('fa-spinner fa-spin');

    fetch(`{{ route("config.report") }}?mode=courseDetails&course=${encodeURIComponent(courseName)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(resp => {
            if (resp && resp.success && resp.details) {
                // Attach details to the row's data so next toggle doesn't refetch
                const updated = row.data();
                updated.details = resp.details;
                row.data(updated);

                row.child(format(resp.details)).show();
                tr.addClass('shown');
                icon.removeClass('fa-spinner fa-spin').addClass('fa-minus');
            } else {
                row.child('<div class="p-3">No details available</div>').show();
                icon.removeClass('fa-spinner fa-spin').addClass('fa-plus');
            }
        })
        .catch(() => {
            row.child('<div class="p-3">Error loading details</div>').show();
            icon.removeClass('fa-spinner fa-spin').addClass('fa-plus');
        });
    });

    // Add plus/minus icons
    $('#coursesTable tbody').on('mouseover', 'tr', function() {
        const icon = $(this).find('.dt-control i');
        if (icon.length === 0) {
            $(this).find('.dt-control').html('<i class="fa fa-plus text-primary btn-details" style="cursor: pointer;"></i>');
        }
    });

    // Event listeners
    $('#refreshBtn').on('click', fetchData);
    $('#dateRange').on('apply.daterangepicker', fetchData);
    $('#locationFilter').on('change', function() {
        fetchData();
    });

    // Initial data load
    fetchData();

    });
</script>
@endpush