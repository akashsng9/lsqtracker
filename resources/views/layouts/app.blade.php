<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LSQ Tracking')</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <!-- DataTables + Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-header">LSQ Tracking</div>
                <ul class="sidebar-menu">
                    <li><a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard') || request()->routeIs('dashboard.index')) active @endif"><i class="fa fa-tachometer"></i> Dashboard</a></li>
                    <li><a href="{{ url('/lead/by-search-parameter') }}" class="@if(request()->is('lead/by-search-parameter')) active @endif"><i class="fa fa-search"></i> All Leads</a></li>
                    <li><a href="{{ url('/lead') }}" class="@if(request()->is('lead')) active @endif"><i class="fa fa-user"></i> Lead details Student</a></li>
                    <li><a href="{{ url('/lead-activity') }}" class="@if(request()->is('lead-activity')) active @endif"><i class="fa fa-history"></i> Lead Activity</a></li>
                    <li><a href="{{ url('/config/team') }}" class="@if(request()->is('config/team')) active @endif"><i class="fa fa-group"></i> Team Config</a></li>
                    <li><a href="{{ url('/config/lead-type') }}" class="@if(request()->is('config/lead-type')) active @endif"><i class="fa fa-tag"></i> Lead Type Config</a></li>

                    <li class="has-submenu">
                        <a href="{{ url('/configuration/team') }}" class="@if(request()->is('configuration/*')) active @endif"><i class="fa fa-cog"></i> Configuration <i class="fa fa-chevron-right"></i></a>
                        <ul class="submenu">
                            <li><a href="{{ route('configuration.course.index') }}"><i class="fa fa-book"></i> Courses</a></li>
                            <li><a href="{{ route('configuration.assigned-courses.index') }}"><i class="fa fa-book"></i> Assigned Courses</a></li>
                            <li><a href="{{ route('lead-sources.index') }}"><i class="fa fa-book"></i> Lead Sources</a></li>
                            <li><a href="{{ route('configuration.tl.index') }}"><i class="fa fa-book"></i> Team Leader</a></li>
                            <li><a href="{{ url('/configuration/lc') }}"><i class="fa fa-book"></i> LC Owners</a></li>
                            <li><a href="{{ url('/team/mumbai') }}"><i class="fa fa-book"></i> Mumbai</a></li>
                            <li><a href="{{ url('/team/gurgaon') }}"><i class="fa fa-book"></i> Gurgaon</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables + Buttons JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

    <!-- Required for Excel/PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            // Initialize multiple DataTables with export buttons
            const idArray = [
                "myTable", "lcTable", "tlTable", "courseTable",
                "locationSummaryTable", "mapLcTable", "assignedTable",
                "sourcesTable", "mumbaiTable", "gurgaonTable"
            ];

            idArray.forEach(function (id) {
                const table = $('#' + id);
                if (table.length) {
                    table.DataTable({
                        dom: 'Bfrtip',
                        buttons: [
                            { extend: 'copyHtml5', text: 'Copy' },
                            { extend: 'excelHtml5', text: 'Excel' },
                            { extend: 'csvHtml5', text: 'CSV' },
                            { extend: 'pdfHtml5', text: 'PDF' },
                            { extend: 'print', text: 'Print' },
                            { extend: 'colvis', text: 'Columns' }
                        ],
                        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                        pageLength: 50
                    });
                }
            });

            // Select2 Initialization
            $('.select2').select2({ width: '100%' });

            $('.select2-location').select2({
                width: '100%',
                tags: true,
                placeholder: 'Select or type location',
                createTag: params => {
                    let term = $.trim(params.term);
                    if (term === '') return null;
                    return { id: term, text: term, newTag: true };
                }
            });

            function initLeadSourceSelect2() {
                $('.select2-source').select2({
                    width: '100%',
                    placeholder: 'Select or type lead source',
                    dropdownParent: $('#addSourceModal'),
                    tags: true,
                    createTag: params => {
                        let term = $.trim(params.term);
                        if (term === '') return null;
                        return { id: term, text: term, newTag: true };
                    },
                    dropdownCssClass: 'select2-dropdown-below'
                }).on('select2:open', () => {
                    document.querySelector('.select2-search__field').focus();
                });
            }

            initLeadSourceSelect2();
            $('#addSourceModal').on('shown.bs.modal', initLeadSourceSelect2);

            // LC Modal Dropdown Fix
            $('#addLcModal').on('shown.bs.modal', function () {
                let $tl = $('#fk_tl');
                if ($tl.length) {
                    if ($tl.data('select2')) $tl.select2('destroy');
                    $tl.select2({ dropdownParent: $('#addLcModal'), width: '100%' });
                }
            });

            // Multi-select with checkboxes
            $('.select2-multiple').select2({
                width: '100%',
                placeholder: 'Select options',
                closeOnSelect: false,
                templateResult: data => {
                    if (!data.id) return data.text;
                    let selected = $(data.element).prop('selected');
                    return $('<span class="select2-item-with-checkbox"><input type="checkbox" class="mr-2" ' + (selected ? 'checked' : '') + ' />' + data.text + '</span>');
                },
                templateSelection: data => data.text,
                escapeMarkup: m => m
            }).on('select2:select select2:unselect', function () {
                const sel = $(this);
                setTimeout(() => {
                    $(sel).data('select2').dropdown.$results.find('.select2-results__option').each(function () {
                        const $opt = $(this);
                        const val = $opt.data('data')?.id;
                        if (!val) return;
                        const isSelected = sel.find(`option[value="${val.replace(/(["\\])/g, '\\$1')}"]`).prop('selected');
                        $opt.find('input[type="checkbox"]').prop('checked', !!isSelected);
                    });
                }, 0);
            });
        });
    </script>

    <!-- Blade stacks -->
    @yield('scripts')
    @stack('scripts')

</body>
</html>
