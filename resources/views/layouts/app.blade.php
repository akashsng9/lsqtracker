<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'LSQ Tracking')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-header">LSQ Tracking</div>
                <ul class="sidebar-menu">
                    <li><a href="{{ route('dashboard') }}" class="@if(request()->routeIs('dashboard') || request()->routeIs('dashboard.index')) active @endif"><i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard</a></li>
                    <li><a href="{{ url('/lead/by-search-parameter') }}" class="@if(request()->is('lead/by-search-parameter')) active @endif"><i class="fa fa-search" aria-hidden="true"></i> All Leads</a></li>
                    <li><a href="{{ url('/lead') }}" class="@if(request()->is('lead')) active @endif"><i class="fa fa-user" aria-hidden="true"></i> Lead details Student</a></li>
                    <li><a href="{{ url('/lead-activity') }}" class="@if(request()->is('lead-activity')) active @endif"><i class="fa fa-history" aria-hidden="true"></i> Lead Activity</a></li>
                    <li><a href="{{ url('/config/team') }}" class="@if(request()->is('config/team')) active @endif"><i class="fa fa-group" aria-hidden="true"></i> Team Config</a></li>
                    <li><a href="{{ url('/config/lead-type') }}" class="@if(request()->is('config/lead-type')) active @endif"><i class="fa fa-tag" aria-hidden="true"></i> Lead Type Config</a></li>
                    <!-- import -->
                    <li><a href="{{ url('/configuration/lead-sources') }}" class="@if(request()->is('configuration/lead-sources')) active @endif"><i class="fa fa-tag" aria-hidden="true"></i> Import Leads Test Data</a></li>
 
                    <li class="has-submenu">
                        <a href="{{ url('/configuration/team') }}" class="@if(request()->is('configuration/*')) active @endif"><i class="fa fa-cog" aria-hidden="true"></i> Configuration <i class="fa fa-chevron-right"></i></a>
                        <ul class="submenu">
                            <li><a href="{{ route('configuration.course.index') }}"><i class="fa fa-book" aria-hidden="true"></i> Courses</a></li>
                            <li><a href="{{ route('configuration.assigned-courses.index') }}"><i class="fa fa-book" aria-hidden="true"></i> Assigned Courses</a></li>
                            <li><a href="{{ route('lead-sources.index') }}"><i class="fa fa-book" aria-hidden="true"></i> Lead Sources</a></li>
                            <li><a href="{{ route('configuration.tl.index') }}"><i class="fa fa-book" aria-hidden="true"></i> Team Leader</a></li>
                            <li><a href="{{ url('/configuration/lc') }}"><i class="fa fa-book" aria-hidden="true"></i> LC Owners</a></li>
                            <li><a href="{{ url('/team/mumbai') }}"><i class="fa fa-book" aria-hidden="true"></i> Mumbai</a></li>
                            <li><a href="{{ url('/team/gurgaon') }}"><i class="fa fa-book" aria-hidden="true"></i> Gurgaon</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>
            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#myTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#lcTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#tlTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#courseTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#locationSummaryTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#mapLcTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#assignedTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            $('#sourcesTable').DataTable(
                {
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "pageLength": 50
                }
            );
            // Initialize Select2
            $('.select2').select2({
                width: '100%'
            });
            // Dynamic location dropdown (supports typing new options)
            $('.select2-location').select2({
                width: '100%',
                tags: true,
                placeholder: 'Select or type location',
                createTag: function (params) {
                    var term = $.trim(params.term);
                    if (term === '') {
                        return null;
                    }
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            // Initialize Select2 for lead source dropdown
            function initLeadSourceSelect2() {
                $('.select2-source').select2({
                    width: '100%',
                    placeholder: 'Select or type lead source',
                    dropdownParent: $('#addSourceModal'),
                    createTag: function (params) {
                        var term = $.trim(params.term);
                        if (term === '') {
                            return null;
                        }
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    },
                    dropdownCssClass: 'select2-dropdown-below',
                    minimumResultsForSearch: 0
                }).on('select2:open', function() {
                    // Focus the search field when dropdown opens
                    document.querySelector('.select2-search__field').focus();
                });
            }
            
            // Initialize when page loads
            initLeadSourceSelect2();
            
            // Reinitialize when modal is shown
            $('#addSourceModal').on('shown.bs.modal', function() {
                initLeadSourceSelect2();
            });
            // Ensure Select2 search works inside Bootstrap modal (LC Add modal)
            // Re-initialize the TL dropdown with dropdownParent to avoid clipping behind modal
            $('#addLcModal').on('shown.bs.modal', function() {
                var $tl = $('#fk_tl');
                if ($tl.length) {
                    // If already initialized, destroy first to reinitialize with dropdownParent
                    if ($tl.data('select2')) {
                        $tl.select2('destroy');
                    }
                    $tl.select2({
                        dropdownParent: $('#addLcModal'),
                        width: '100%'
                    });
                }
            });
            // Multi-select with checkboxes inside dropdown
            $('.select2-multiple').select2({
                width: '100%',
                placeholder: 'Select options',
                closeOnSelect: false,
                templateResult: function (data) {
                    if (!data.id) {
                        return data.text; // optgroup or placeholder
                    }
                    var selected = $(data.element).prop('selected');
                    var $result = $(
                        '<span class="select2-item-with-checkbox">'
                        + '<input type="checkbox" class="mr-2" ' + (selected ? 'checked' : '') + ' />'
                        + '<span>' + data.text + '</span>'
                        + '</span>'
                    );
                    return $result;
                },
                templateSelection: function (data) {
                    return data.text;
                },
                escapeMarkup: function (m) { return m; }
            }).on('select2:select select2:unselect', function (e) {
                // Update checkboxes immediately when selection changes
                var sel = $(this);
                setTimeout(function(){
                    $(sel).data('select2').dropdown.$results.find('.select2-results__option').each(function(){
                        var $opt = $(this);
                        var id = $opt.attr('id');
                        if (!id) return;
                        var val = $opt.data('data') && $opt.data('data').id;
                        if (!val) return;
                        var isSelected = sel.find('option[value="'+val.replace(/(["\\])/g,'\\$1')+'"]').prop('selected');
                        $opt.find('input[type="checkbox"]').prop('checked', !!isSelected);
                    });
                }, 0);
            });
        });
    </script>
    
    <!-- Stack for scripts -->
    @yield('scripts')
    @stack('scripts')
</body>

</html>