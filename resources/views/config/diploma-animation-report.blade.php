@extends('layouts.app')

@section('title', 'Courses Performance Dashboard')

@push('styles')
<!-- Additional styles specific to this page -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css" />
<style>
    .card {
        margin-bottom: 2rem;
        box-shadow: 0 0.15rem 0.5rem rgba(0, 0, 0, 0.1);
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
    }

    .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 1rem 1.25rem;
    }

    .card-header h6 {
        font-weight: 600;
        color: #4e73df;
        margin: 0;
    }

    .table th {
        background-color: #f8f9fc;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        color: #4e73df;
    }

    .table td {
        vertical-align: middle;
    }

    .table> :not(caption)>*>* {
        padding: 0.75rem;
    }

    .table-hover>tbody>tr:hover {
        background-color: rgba(0, 0, 0, .01);
    }

    .table-info {
        --bs-table-bg: #f1f8ff;
    }

    .table-secondary {
        --bs-table-bg: #f8f9fa;
        font-weight: 600;
    }

    .ps-4 {
        padding-left: 1.5rem !important;
    }

    .metric-card {
        border-radius: 0.35rem;
        border-left: 4px solid #4e73df;
        margin-bottom: 1.5rem;
        transition: transform 0.2s;
    }

    .metric-card:hover {
        transform: translateY(-3px);
    }

    .metric-card .card-body {
        padding: 1.25rem;
    }

    .metric-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2e59d9;
        line-height: 1.2;
    }

    .metric-label {
        color: #5a5c69;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .progress {
        height: 0.5rem;
        margin-top: 0.5rem;
        border-radius: 0.25rem;
    }

    .btn-view-report {
        border-radius: 1.5rem;
        padding: 0.4rem 1.2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.2s;
    }

    .bg-gradient-primary {
        background: linear-gradient(45deg, #4e73df, #224abe);
    }

    .text-primary {
        color: #4e73df !important;
    }

    .text-success {
        color: #1cc88a !important;
    }

    .text-info {
        color: #36b9cc !important;
    }

    .text-warning {
        color: #f6c23e !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line text-primary"></i> Courses Performance Dashboard
        </h1>
        <div class="d-none d-sm-inline-block">
            <div class="input-group">
                <select class="form-control form-control-sm">
                    <option>All Courses</option>
                    <option>Diploma in Animation</option>
                    <option>Graphic Design</option>
                    <option>Web Development</option>
                </select>
                <button class="btn btn-primary btn-sm ml-2">
                    <i class="fas fa-download fa-sm"></i> Export
                </button>
            </div>
        </div>
    </div>

    <!-- Courses Overview Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-table me-2"></i>Courses Overview
                    </h6>
                    <div class="dropdown no-arrow
                    ">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Export to Excel</a></li>
                            <li><a class="dropdown-item" href="#">Print</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="coursesTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Course Name</th>
                                    <th>Total Leads</th>
                                    <th>Enrollment (LSQ)</th>
                                    <th>Enrollment (DB)</th>
                                    <th>Conversion</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Diploma in Animation -->
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">Diploma in Animation</div>
                                        <div class="text-muted small">3 Months</div>
                                    </td>
                                    <td>1,200</td>
                                    <td>50</td>
                                    <td>30</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-sm mr-2" style="width: 4rem">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span>2.5%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-course="Diploma in Animation">
                                            View Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Other Courses (Example) -->
                                <tr>
                                    <td>
                                        <div class="font-weight-bold">Graphic Design</div>
                                        <div class="text-muted small">2 Months</div>
                                    </td>
                                    <td>950</td>
                                    <td>42</td>
                                    <td>25</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-sm mr-2" style="width: 4rem">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 35%" aria-valuenow="35" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span>3.5%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-course="Diploma in Animation">
                                            View Details
                                        </button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="font-weight-bold">Web Development</div>
                                        <div class="text-muted small">4 Months</div>
                                    </td>
                                    <td>1,500</td>
                                    <td>65</td>
                                    <td>45</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-sm mr-2" style="width: 4rem">
                                                <div class="progress-bar bg-info" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <span>3.0%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-details" data-course="Diploma in Animation">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Breakdown -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-funnel-dollar me-2"></i>Lead Source Breakdown
                    </h6>
                    <button class="btn btn-sm btn-primary btn-view-report">
                        View Full Report <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="topCityTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>City</th>
                                    <th>Leads</th>
                                    <th>Enrollments</th>
                                    <th>Conversion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-info">
                                    <td><strong>Total Leads - Paid</strong></td>
                                    <td>800</td>
                                    <td>43</td>
                                    <td>20</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Facebook</td>
                                    <td>600</td>
                                    <td>35</td>
                                    <td>15</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Google</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>Total Leads - Organic</strong></td>
                                    <td>400</td>
                                    <td>10</td>
                                    <td>10</td>
                                    <td>2.5%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Breakdown -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-funnel-dollar me-2"></i>Lead Source Breakdown
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
                            <tbody>
                                <tr class="table-info">
                                    <td><strong>Total Leads - Paid</strong></td>
                                    <td>800</td>
                                    <td>43</td>
                                    <td>20</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Facebook</td>
                                    <td>600</td>
                                    <td>35</td>
                                    <td>15</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Google</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr class="table-info">
                                    <td><strong>Total Leads - Organic</strong></td>
                                    <td>400</td>
                                    <td>10</td>
                                    <td>10</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Google Organic</td>
                                    <td>400</td>
                                    <td>10</td>
                                    <td>10</td>
                                    <td>2.5%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Lead Performance -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-tie me-2"></i>Team Leads Performance
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
                            <tbody>
                                <tr>
                                    <td><strong>Akash</strong></td>
                                    <td>Abhishek</td>
                                    <td>300</td>
                                    <td>30</td>
                                    <td>10</td>
                                    <td>3.33%</td>
                                </tr>
                                <tr>
                                    <td><strong>Alok</strong></td>
                                    <td>-</td>
                                    <td>430</td>
                                    <td>23</td>
                                    <td>12</td>
                                    <td>2.79%</td>
                                </tr>
                                <tr>
                                    <td><strong>Archit</strong></td>
                                    <td>-</td>
                                    <td>120</td>
                                    <td>29</td>
                                    <td>4</td>
                                    <td>3.33%</td>
                                </tr>
                                <tr>
                                    <td><strong>Abhishai</strong></td>
                                    <td>-</td>
                                    <td>110</td>
                                    <td>11</td>
                                    <td>5</td>
                                    <td>4.55%</td>
                                </tr>
                                <tr class="table-secondary">
                                    <td colspan="2"><strong>Total</strong></td>
                                    <td>960</td>
                                    <td>93</td>
                                    <td>31</td>
                                    <td>3.23%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversion Analysis -->
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
                                    <th>Team Lead</th>
                                    <th>Total Leads</th>
                                    <th>Paid Leads</th>
                                    <th>Organic Leads</th>
                                    <th>Conversion (Overall)</th>
                                    <th>Conversion (Paid)</th>
                                    <th>Conversion (Organic)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Abhishek</strong></td>
                                    <td>300</td>
                                    <td>200</td>
                                    <td>100</td>
                                    <td>16.67%</td>
                                    <td>15.00%</td>
                                    <td>20.00%</td>
                                </tr>
                                <tr>
                                    <td><strong>Alok</strong></td>
                                    <td>430</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td><strong>Archit</strong></td>
                                    <td>120</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td><strong>Abhishai</strong></td>
                                    <td>110</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Cities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt me-2"></i>Top 5 Cities by Conversion
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Lead Source Breakdown -->
                    <div class="table-responsive">
                        <table id="topCityTable" class="table table-bordered" width="100%" cellspacing="0">
                            <thead class="bg-light">
                                <tr>
                                    <th>City</th>
                                    <th>Leads</th>
                                    <th>Enrollments</th>
                                    <th>Conversion</th>
                                    <th>Conversion Rate</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td class="ps-4">Delhi</td>
                                    <td>600</td>
                                    <td>35</td>
                                    <td>15</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Gurgaon</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Noida</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Punjab</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>
                                <tr>
                                    <td class="ps-4">Faridabad</td>
                                    <td>200</td>
                                    <td>8</td>
                                    <td>5</td>
                                    <td>2.5%</td>
                                </tr>

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
<!-- Required DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Courses Table
        var table = $('#coursesTable').DataTable({
                dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                order: [
                    [1, 'desc']
                ],
                buttons: [{
                        extend: 'copyHtml5',
                        className: 'btn btn-sm btn-secondary mb-2',
                        text: '<i class="fas fa-copy"></i> Copy',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-sm btn-success mb-2',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-sm btn-danger mb-2',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-info mb-2',
                        text: '<i class="fas fa-print"></i> Print',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'excelHtml5',
                    className: 'btn btn-sm btn-success mb-2',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                },
                {
                    extend: 'pdfHtml5',
                    className: 'btn btn-sm btn-danger mb-2',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    },
                    customize: function(doc) {
                        doc.defaultStyle.fontSize = 10;
                        doc.styles.tableHeader.fontSize = 10;
                    }
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info mb-2',
                    text: '<i class="fas fa-print"></i> Print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4]
                    }
                }
            ]
        });

    // Handle View Details button clicks using event delegation
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const course = $(this).data('course');
        const modal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));

        // Update modal title
        $('#courseDetailsModalLabel').text(course + ' - Detailed Report');

        // Show loading state
        $('#courseDetailsBody').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading details for ${course}...</p>
                </div>
            `);

        // Show the modal
        modal.show();

        // Simulate loading data (replace with actual AJAX call)
        setTimeout(() => {
            $('#courseDetailsBody').html(`
                    <div class="course-details">
                        <h5 class="mb-4">${course} Performance Overview</h5>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Total Leads</h6>
                                        <h3 class="card-title text-primary">1,200</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Enrollment (LSQ)</h6>
                                        <h3 class="card-title text-success">50</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Enrollment (DB)</h6>
                                        <h3 class="card-title text-info">30</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h6 class="card-subtitle mb-2 text-muted">Conversion Rate</h6>
                                        <h3 class="card-title text-warning">2.5%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mt-4 mb-3">Top 5 Cities</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>City</th>
                                        <th>Leads</th>
                                        <th>Enrollments</th>
                                        <th>Conversion</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Mumbai</td>
                                        <td>450</td>
                                        <td>25</td>
                                        <td>5.6%</td>
                                    </tr>
                                    <tr>
                                        <td>Delhi</td>
                                        <td>380</td>
                                        <td>18</td>
                                        <td>4.7%</td>
                                    </tr>
                                    <tr>
                                        <td>Bangalore</td>
                                        <td>320</td>
                                        <td>15</td>
                                        <td>4.7%</td>
                                    </tr>
                                    <tr>
                                        <td>Hyderabad</td>
                                        <td>290</td>
                                        <td>12</td>
                                        <td>4.1%</td>
                                    </tr>
                                    <tr>
                                        <td>Pune</td>
                                        <td>260</td>
                                        <td>10</td>
                                        <td>3.8%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                `);
        }, 500);
    });

    // Initialize the city table
    var cityTable = $('#topCityTable').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ]
    });

    // Add buttons to the table
    new $.fn.dataTable.Buttons(cityTable, {
        buttons: [{
                extend: 'copyHtml5',
                className: 'btn btn-sm btn-secondary',
                text: '<i class="fas fa-copy"></i> Copy'
            },
            {
                extend: 'excelHtml5',
                className: 'btn btn-sm btn-success',
                text: '<i class="fas fa-file-excel"></i> Excel'
            },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-sm btn-danger',
                text: '<i class="fas fa-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-info',
                text: '<i class="fas fa-print"></i> Print'
            }
        ]
    });

    // Add buttons to the table header
    $('.dataTables_filter', $('#topCityTable_wrapper')).append($('.dt-buttons'));

    // Course filter change handler
    $('select.form-control').on('change', function() {
        const selectedCourse = $(this).val();
        const table = $('#coursesTable').DataTable();
        if (selectedCourse === 'All Courses') {
            table.search('').columns().search('').draw();
        } else {
            table.column(0).search(selectedCourse).draw();
        }
    });

    // Initialize modals
    var courseDetailsModal = new bootstrap.Modal(document.getElementById('courseDetailsModal'));

    // Handle View Details button clicks
    $(document).on('click', '.view-details', function(e) {
        e.preventDefault();
        const course = $(this).data('course');
        $('#courseDetailsModalLabel').text(course + ' - Detailed Report');
        courseDetailsModal.show();
    });

    // Close button for modals
    $('[data-bs-dismiss="modal"]').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });
    });
</script>

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1" aria-labelledby="courseDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="courseDetailsModalLabel">Course Details</h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="courseDetailsBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading course details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Make sure DataTables is available
    if (typeof $().DataTable === 'function') {
        $(document).ready(function() {
            // Initialize DataTables
            var table = $('.table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });

            // Handle View Details button
            $(document).on('click', '.view-details', function() {
                var course = $(this).data('course');
                // You can add AJAX call here to load course details
                console.log('View details for: ' + course);
            });
        });
</script>
@endpush
@endpush