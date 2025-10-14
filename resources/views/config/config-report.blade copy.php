@extends('layouts.app')

@section('title', 'Courses Report')

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css" />
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> -->
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

                    <div class="row">
                    <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body bg-primary text-white ">
                                    <div class="d-flex">
                                        <div class="mr-auto my-auto">
                                            <i class="fa fa-users" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="text-right">
                                            <h6 class="card-title">Total Leads</h6>
                                            <h2 class="card-text">1000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body bg-warning text-dark">
                                    <div class="d-flex">
                                        <div class="mr-auto my-auto">
                                            <i class="fa fa-users" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="text-right">
                                            <h6 class="card-title">Leads (LSQ)</h6>
                                            <h2 class="card-text">1000</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body bg-danger text-white">
                                <div class="d-flex">
                                        <div class="mr-auto my-auto">
                                            <i class="fa fa-users" style="font-size: 2rem;"></i>
                                        </div>
                                        <div class="text-right">
                                            <h6 class="card-title">Leads (DB)</h6>
                                            <h2 class="card-text">900</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-body bg-success text-white">
                                    <div class="d-flex">
                                        <div class="mr-auto my-auto">
                                        <i class="fa fa-line-chart" style="font-size: 2rem;" aria-hidden="true"></i>
                                        </div>
                                        <div class="text-right">
                                            <h6 class="card-title">Conversion</h6>
                                            <h2 class="card-text">300</h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <hr>

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
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                            <tr data-details="{{ json_encode($course['details']) }}">
                                <td class="dt-control"></td>
                                <td>{{ $course['id'] }}</td>
                                <td>
                                    <strong>{{ $course['name'] }}</strong>
                                    <div class="text-muted small">{{ $course['details']['duration'] }}</div>
                                </td>
                                <td>{{ number_format($course['total_leads']) }}</td>
                                <td>{{ $course['enrollment_lsq'] }}</td>
                                <td>{{ $course['enrollment_db'] }}</td>
                                <td>
                                    <span class="badge bg-{{ $course['conversion'] > 5 ? 'success' : ($course['conversion'] > 3 ? 'warning' : 'danger') }}">
                                        {{ $course['conversion'] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                    $statusClass = [
                                    'Active' => 'success',
                                    'Inactive' => 'danger',
                                    'Draft' => 'warning'
                                    ][$course['status']] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $course['status'] }}</span>
                                </td>
                                <td>
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
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
<script>
    $(document).ready(function() {
        function format(details) {
            return `
                <div class="details-content">
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
                                                    <th>TLs Performance</th>
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

            `;
        }

        var table = $('#coursesTable').DataTable({
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [{
                    extend: 'excel',
                    className: 'btn btn-sm btn-success me-2 mb-2',
                    text: '<i class="fa fa-file-excel me-1"></i> Excel'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger me-2 mb-2',
                    text: '<i class="fa fa-file-pdf me-1"></i> PDF'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-secondary mb-2',
                    text: '<i class="fa fa-print me-1"></i> Print'
                }
            ],
            pageLength: 10,
            order: [
                [1, 'asc']
            ],
            responsive: true,
            columnDefs: [{
                    className: 'dt-control',
                    orderable: false,
                    data: null,
                    defaultContent: '',
                    targets: 0
                },
                {
                    orderable: true,
                    targets: [1, 2, 3, 4, 5, 6, 7]
                }
            ]
        });

        // Add event listener for opening and closing details
        $('#coursesTable tbody').on('click', 'td.dt-control', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            var icon = $(this).find('i');

            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa fa-solid fa-minus').addClass('fa fa-solid fa-plus');
            } else {
                var details = JSON.parse(tr.attr('data-details'));
                row.child(format(details)).show();
                tr.addClass('shown');
                icon.removeClass('fa fa-solid fa-plus').addClass('fa fa-solid fa-minus');
            }
        });

        // Add plus/minus icons
        $('#coursesTable tbody tr').each(function() {
            $(this).find('td:first').html('<i class="fa fa fa-solid fa-plus text-primary btn-details" style="cursor: pointer;"></i>');
        });
    });
</script>
@endpush