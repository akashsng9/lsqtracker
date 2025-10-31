<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0); // unlimited
ini_set('memory_limit', '2G');

use App\Jobs\FetchLeadsJob;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\TeamConfig;
use App\Models\TLMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TeamConfigController extends Controller
{
    /**
     * Check if data is still being processed
     */
    public function checkProcessingStatus()
    {
        $isProcessing = Cache::get('dashboard_processing', false);
        return response()->json(['isProcessing' => $isProcessing]);
    }
    /**
     * Display the Diploma in Animation performance report
     *
     * @return \Illuminate\View\View
     */
    public function diplomaAnimationReport()
    {
        // This is a static report, so we don't need to pass any data
        // All data is hardcoded in the view
        return view('config.diploma-animation-report');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    // Show mapping form to associate Courses to a specific LC
    public function mapCourses(int $lcId, Request $request)
    {
        $lc = DB::table('tbl_lead_owner_lc_master')->where('id', $lcId)->first();
        abort_if(!$lc, 404);

        $selectedLocation = $request->query('location');

        // Build locations for filtering courses
        $locations = DB::table('tbl_course_master')
            ->select('courseLocation')
            ->whereNotNull('courseLocation')
            ->groupBy('courseLocation')
            ->orderBy('courseLocation')
            ->pluck('courseLocation');

        $courseQuery = DB::table('tbl_course_master')
            ->select('id', 'courseName', 'courseLocation', 'CourseStatus');

        if (!empty($selectedLocation)) {
            $courseQuery->where('courseLocation', $selectedLocation);
        }

        // Prefer active courses first
        $courses = $courseQuery->orderByDesc('CourseStatus')->orderBy('courseName')->get();

        // Existing mappings for this LC
        $mappedCourseIds = DB::table('tbl_lc_course_master')
            ->where('fk_lc', $lcId)
            ->pluck('fk_course')
            ->toArray();

        return view('config.lc-map-courses', [
            'lc' => $lc,
            'courses' => $courses,
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'mappedCourseIds' => $mappedCourseIds,
        ]);
    }

    // Save mapping Courses for a specific LC
    public function saveMapCourses(int $lcId, Request $request)
    {
        $validated = $request->validate([
            'course_ids' => 'array',
            'course_ids.*' => 'integer|exists:tbl_course_master,id',
        ]);

        $courseIds = collect($validated['course_ids'] ?? [])->unique()->values()->all();

        DB::transaction(function () use ($lcId, $courseIds) {
            DB::table('tbl_lc_course_master')->where('fk_lc', $lcId)->delete();

            $now = now();
            $rows = [];
            foreach ($courseIds as $courseId) {
                $rows[] = [
                    'fk_lc' => (int)$lcId,
                    'fk_course' => (int)$courseId,
                    'created_on' => $now,
                    'updated_on' => $now,
                    'updated_by' => null,
                ];
            }
            if (!empty($rows)) {
                DB::table('tbl_lc_course_master')->insert($rows);
            }
        });

        return redirect()->route('configuration.lc.index')
            ->with('success', 'Courses mapped successfully.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TeamConfig $teamConfig)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TeamConfig $teamConfig)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TeamConfig $teamConfig)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TeamConfig $teamConfig)
    {
        //
    }

    // Configuration Methods
    public function getTeamConfig()
    {
        $perPage = 20; // reduce memory by paginating results
        $storedConfigLeads = \App\Models\TeamConfig::orderByDesc('CreatedOn')->paginate($perPage);
        return view('config.team-config', ['storedConfigLeads' => $storedConfigLeads]);
    }


    // Controller
    public function saveTeamConfig(Request $request)
    {
        // dispatch(new \App\Jobs\FetchLeadsJob($request->all()));

        // return response()->json([
        //     'message' => 'Leads fetching started in background. Please check logs or DB later.'
        // ]);

        // Dispatch the background job with the request params
        FetchLeadsJob::dispatch($request->all());

        return redirect()
            ->route('config.team')
            ->with('status', 'Lead fetching has started. Check back later for results.');
    }



    public function getLeadTypeConfig()
    {
        $leadTypes = \App\Models\TeamConfig::all();
        return view('config.lead-type-config', ['leadTypes' => $leadTypes]);
    }

    public function saveLeadTypeConfig(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        \App\Models\TeamConfig::create([
            'type_name' => $request->input('type_name'),
            'description' => $request->input('description'),
        ]);
        return redirect()->route('config.lead-type')->with('success', 'Lead type configuration saved.');
    }

    public function mumbai()
    {
        // Get filter options
        $courses = DB::table('tbl_course_master')
            ->select('courseName')
            ->orderBy('courseName')
            ->get();

        $lcs = DB::table('tbl_lead_owner_lc_master')
            ->select('id', 'lcName', 'location', 'status')
            ->where('status', 1)
            ->orderBy('lcName')
            ->get();

        $teamLead = TLMaster::where('status', '1')
            ->select('id', 'tl_name')
            ->get();

        $leadType = LeadSource::where('status', '1')
            ->select('sourceType')
            ->distinct()
            ->get();

        // Get initial data (first page, no filters) - Updated to match new table structure
        $leads = DB::table('tbl_course_master')
            ->select([
                'tbl_course_master.courseName',
                'tbl_tl_master.tl_name',
                'tbl_lead_owner_lc_master.lcName',
                'tbl_lead_source_master.sourceType as lead_source_type',
                DB::raw('COUNT(leads.id) as totalLeads'),
                DB::raw('SUM(CASE WHEN leads.prospectStage = "Enrolled" THEN 1 ELSE 0 END) as totalEnrollment')
            ])
            ->leftJoin('tbl_lc_course_master', 'tbl_course_master.id', '=', 'tbl_lc_course_master.fk_course')
            ->leftJoin('tbl_lead_owner_lc_master', 'tbl_lc_course_master.fk_lc', '=', 'tbl_lead_owner_lc_master.id')
            ->leftJoin('tbl_tl_master', 'tbl_lead_owner_lc_master.fk_tl', '=', 'tbl_tl_master.id')
            ->leftJoin('leads', function ($join) {
                $join->on('leads.mx_Lead_Course', '=', 'tbl_course_master.courseName')
                    ->on('leads.OwnerIdName', '=', 'tbl_lead_owner_lc_master.lcName');
            })
            ->leftJoin('tbl_lead_source_master', 'leads.Source', '=', 'tbl_lead_source_master.leadSource')
            ->where('tbl_lead_owner_lc_master.status', 1)
            ->where('tbl_tl_master.status', 1)
            ->groupBy([
                'tbl_course_master.courseName',
                'tbl_tl_master.tl_name',
                'tbl_lead_owner_lc_master.lcName',
                'tbl_lead_source_master.sourceType'
            ])
            ->orderBy('tbl_course_master.courseName')
            ->paginate(25);

        return view('config.team-mumbai', compact('courses', 'lcs', 'leads', 'teamLead', 'leadType'));
    }

    public function filterMumbai(Request $request)
    {
        // Start building the query - Updated to match new table structure
        $query = DB::table('tbl_course_master')
            ->select([
                'tbl_course_master.courseName',
                'tbl_tl_master.tl_name',
                'tbl_lead_owner_lc_master.lcName',
                'tbl_lead_source_master.sourceType as lead_source_type',
                DB::raw('COUNT(leads.id) as totalLeads'),
                DB::raw('SUM(CASE WHEN leads.prospectStage = "Enrolled" THEN 1 ELSE 0 END) as totalEnrollment')
            ])
            ->leftJoin('tbl_lc_course_master', 'tbl_course_master.id', '=', 'tbl_lc_course_master.fk_course')
            ->leftJoin('tbl_lead_owner_lc_master', 'tbl_lc_course_master.fk_lc', '=', 'tbl_lead_owner_lc_master.id')
            ->leftJoin('tbl_tl_master', 'tbl_lead_owner_lc_master.fk_tl', '=', 'tbl_tl_master.id')
            ->leftJoin('leads', function ($join) {
                $join->on('leads.mx_Lead_Course', '=', 'tbl_course_master.courseName')
                    ->on('leads.OwnerIdName', '=', 'tbl_lead_owner_lc_master.lcName');
            })
            ->leftJoin('tbl_lead_source_master', 'leads.Source', '=', 'tbl_lead_source_master.leadSource')
            ->where('tbl_lead_owner_lc_master.status', 1)
            ->where('tbl_tl_master.status', 1);

        // Apply filters
        if ($request->has('course_name') && !empty($request->course_name)) {
            $query->whereIn('tbl_course_master.courseName', (array)$request->course_name);
        }

        if ($request->has('tl_name') && !empty($request->tl_name)) {
            $query->whereIn('tbl_tl_master.id', (array)$request->tl_name);
        }

        if ($request->has('lc_name') && !empty($request->lc_name)) {
            $query->whereIn('tbl_lead_owner_lc_master.id', (array)$request->lc_name);
        }

        if ($request->has('lead_type') && !empty($request->lead_type)) {
            $query->where('tbl_lead_source_master.sourceType', $request->lead_type);
        }

        // Group and order the results
        $leads = $query->groupBy([
            'tbl_course_master.courseName',
            'tbl_tl_master.tl_name',
            'tbl_lead_owner_lc_master.lcName',
            'tbl_lead_source_master.sourceType'
        ])
            ->orderBy('tbl_course_master.courseName')
            ->paginate(25);

        // Return JSON response for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $leads->items(),
                'pagination' => [
                    'current_page' => $leads->currentPage(),
                    'last_page' => $leads->lastPage(),
                    'per_page' => $leads->perPage(),
                    'total' => $leads->total(),
                    'from' => $leads->firstItem(),
                    'to' => $leads->lastItem(),
                    'path' => $leads->path(),
                    'next_page_url' => $leads->nextPageUrl(),
                    'prev_page_url' => $leads->previousPageUrl(),
                ]
            ]);
        }

        // For non-AJAX requests, return the view with data
        $courses = DB::table('tbl_course_master')
            ->select('courseName')
            ->orderBy('courseName')
            ->get();

        $lcs = DB::table('tbl_lead_owner_lc_master')
            ->select('id', 'lcName', 'location', 'status')
            ->where('status', 1)
            ->orderBy('lcName')
            ->get();

        $teamLead = TLMaster::where('status', '1')
            ->select('id', 'tl_name')
            ->get();

        $leadType = LeadSource::where('status', '1')
            ->select('sourceType')
            ->distinct()
            ->get();

        return view('config.team-mumbai', compact('leads', 'courses', 'lcs', 'teamLead', 'leadType'));
    }

    public function gurgao()
    {
        $courses = DB::table('tbl_course_master')
            ->select('courseName')
            // ->where('courseLocation', 'Gurugram')
            ->orderBy('courseName')
            ->get();

        $lcs = DB::table('tbl_lead_owner_lc_master')
            ->select('id', 'lcName', 'location', 'status')
            // ->where('location', 'Gurugram')
            ->where('status', 1)
            ->orderBy('lcName')
            ->get();

        $leads = DB::table('leads')
            ->select('id', 'FirstName', 'LastName', 'Phone')
            ->orderBy('FirstName')
            ->limit(1000)
            ->get();

        $teamLead = TLMaster::where('status', '1')
            ->select('id', 'tl_name')
            ->get();

        $leadType = LeadSource::where('status', '1')
            ->select('sourceType')
            ->distinct()
            ->get();

        return view('config.team-gurgao', compact('courses', 'lcs', 'leads', 'teamLead', 'leadType'));
    }

    // LC management: list and update status
    public function lcIndex(Request $request)
    {
        // Optional filter by location via query param
        $selectedLocation = $request->query('location');
        $selectedTl = $request->query('tl');

        $locations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('location')
            ->pluck('location');

        // Fetch TLs for filter dropdown and TL assignment
        $tls = DB::table('tbl_tl_master')
            ->select('id', 'tl_name')
            ->whereNull('deleted_at')
            ->orderBy('tl_name')
            ->get();
        // dd($tls);

        $query = DB::table('tbl_lead_owner_lc_master')
            ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'tbl_lead_owner_lc_master.fk_tl')
            ->leftJoin('tbl_lc_course_master as lcc', 'lcc.fk_lc', '=', 'tbl_lead_owner_lc_master.id')
            ->select('tbl_lead_owner_lc_master.id', 'tbl_lead_owner_lc_master.lcName', 'tbl_lead_owner_lc_master.location', 'tbl_lead_owner_lc_master.status', 'tbl_lead_owner_lc_master.updatedAt', DB::raw('tl.tl_name as tl_name'), 'tbl_lead_owner_lc_master.fk_tl', DB::raw('(select courseName  from tbl_course_master where id = lcc.fk_course limit 1) as courseName'))
            ->orderBy('tbl_lead_owner_lc_master.location')
            ->orderBy('tbl_lead_owner_lc_master.lcName')
            ->whereNull('tl.deleted_at');

        if (!empty($selectedLocation)) {
            $query->where('tbl_lead_owner_lc_master.location', $selectedLocation);
        }

        if (!empty($selectedTl)) {
            $query->where('tbl_lead_owner_lc_master.fk_tl', (int)$selectedTl);
        }

        $lcs = $query->get();
        // dd($lcs);

        // Count mapped courses per LC for badge display
        $lcCourseCounts = DB::table('tbl_lc_course_master')
            ->select('fk_lc', DB::raw('COUNT(*) as cnt'))
            ->groupBy('fk_lc')
            ->pluck('cnt', 'fk_lc');

        $data = [
            'lcs' => $lcs,
            'locations' => $locations,
            'tls' => $tls,
            'selectedLocation' => $selectedLocation,
            'selectedTl' => $selectedTl,
            'lcCourseCounts' => $lcCourseCounts,
        ];

        // Debug: Log the data being passed to the view
        Log::info('Data being passed to lc-index view:', $data);

        return view('config.lc-index', $data);
    }

    public function updateLcStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1',
        ]);

        DB::table('tbl_lead_owner_lc_master')
            ->where('id', $id)
            ->update([
                'status' => (int)$validated['status'],
                'updatedAt' => now(),
            ]);

        return back()->with('success', 'LC status updated successfully.');
    }

    public function storeLc(Request $request)
    {
        $validated = $request->validate([
            'lcName'   => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'fk_tl'    => [
                'required',
                'integer',
                Rule::exists('tbl_tl_master', 'id')->whereNull('deleted_at'),
            ],
            'status'   => 'required|in:0,1',
        ]);

        DB::table('tbl_lead_owner_lc_master')->insert([
            'lcName'   => $validated['lcName'],
            'location' => $validated['location'],
            'fk_tl'    => (int)$validated['fk_tl'],
            'status'   => (int)$validated['status'],
            'createdAt' => now(),
            'updatedAt' => now(),
            'deletedBy' => null,
            'updatedBy' => null,
        ]);

        return redirect()->route('configuration.lc.index', ['location' => $validated['location']])
            ->with('success', 'LC added successfully.');
    }

    /**
     * Save TL mapping for an LC
     */
    public function saveMapTl($lcId, Request $request)
    {
        $validated = $request->validate([
            'tl_id' => [
                'nullable',
                'integer',
                Rule::exists('tbl_tl_master', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $lc = DB::table('tbl_lead_owner_lc_master')->find($lcId);
        if (!$lc) {
            return back()->with('error', 'LC not found.');
        }

        DB::table('tbl_lead_owner_lc_master')
            ->where('id', $lcId)
            ->update([
                'fk_tl' => $validated['tl_id'] ? (int)$validated['tl_id'] : null,
                'updatedAt' => now(),
            ]);

        return back()->with('success', 'TL mapping updated successfully.');
    }

    public function showDashboard_31Oct(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $startTime = microtime(true);
        $mode = $request->query('mode');

        // 🟢 Handle non-AJAX (initial page load)
        if (!$request->ajax() && !$request->wantsJson() && !$mode) {
            return view('config.config-report');
        }

        // 🔵 Log every call for debugging
        Log::info('showDashboard called', [
            'mode' => $mode,
            'ajax' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ]);

        // Handle mode-based auxiliary actions
        if ($mode === 'courseDetails') {
            return $this->showCourseDetails($request);
        }
        if ($mode === 'status') {
            $isProcessing = Cache::get('dashboard_processing', false);
            return response()->json(['isProcessing' => $isProcessing]);
        }

        // 🧩 Base Queries
        $salesQuery = DB::connection('mysql_secondary')->table('sales')->where('status', 1);
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->whereIn('status', ['verified', 'un-verified']);

        // 🔍 Filters
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 1000);

        // 🕒 Apply date filters dynamically
        $applyDateFilter = function ($query) use ($dateRange, $fromDate, $toDate) {
            if ($dateRange) {
                switch ($dateRange) {
                    case 'yesterday':
                        $query->whereDate('created_at', now()->subDay()->toDateString());
                        break;
                    case 'weekly':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->subDays(30));
                        break;
                    case 'yearly':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            } elseif ($fromDate && $toDate) {
                $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            } else {
                $query->where('created_at', '>=', now()->subMonths(6));
            }
        };

        $applyDateFilter($salesQuery);
        $applyDateFilter($directPaymentQuery);

        if ($location) {
            $salesQuery->where('location', $location);
            $directPaymentQuery->where('location', $location);
        }

        // Total Leads from Leads Table
        $totalLeadsFromLeadTable = DB::table('leads')->where('created_at', '>', '2025-04-01')->count();

        // Paginated Data Fetch
        $sales = $salesQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $directPayments = $directPaymentQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();

        // Process data
        $newSalesArray = $sales->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'email' => $item->email,
            'contact' => $item->contact,
        ])->toArray();

        $newDirectPaymentArray = $directPayments->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'email' => $item->email,
            'contact' => $item->contact,
        ])->toArray();

        $salesUsers = collect(array_merge($newSalesArray, $newDirectPaymentArray));

        // 🧩 Extract emails & contacts
        $emails = $salesUsers->pluck('email')->filter()->unique()->toArray();
        $contacts = $salesUsers->pluck('contact')->filter()->unique()->toArray();

        // 🧮 Leads Matching with Sales / Direct Payments
        $totalSalesDBLeads = DB::table('leads')
            ->where(function ($q) use ($emails, $contacts) {
                if ($emails) $q->whereIn('EmailAddress', $emails);
                if ($contacts) $q->orWhereIn('Phone', $contacts);
            })
            // ->take(1000)
            ->count();

        // 📊 Cached Global Stats
        $dashboardStats = Cache::remember('dashboard_summary', 600, function () {
            return [
                'totalDbLeads' => DB::table('leads')->where('created_at', '>', '2025-04-01')->count(),
                'totalConversion' => DB::table('leads')
                    ->where('prospectStage', 'Enrolled')
                    ->where('created_at', '>', '2025-04-01')
                    ->count(),
            ];
        });

        // dd($totalSalesDBLeads);

        // 📘 Cached Course-Wise Lead Stats
        $courseLeads = Cache::remember('dashboard_courses', 600, function () {
            return DB::table('tbl_course_master')
                ->select([
                    'tbl_course_master.id',
                    'tbl_course_master.courseName',
                    DB::raw('COUNT(leads.id) as total_leads'),
                    DB::raw('SUM(CASE WHEN leads.prospectStage = "Enrolled" THEN 1 ELSE 0 END) as enrollment_db'),
                    DB::raw('MAX(leads.updated_at) as last_updated')
                ])
                ->leftJoin('leads', 'leads.mx_Course_Interested', '=', 'tbl_course_master.courseName')
                ->groupBy('tbl_course_master.id', 'tbl_course_master.courseName')
                ->orderBy('tbl_course_master.courseName')
                ->get();
        });

        info("✅ Aggregate query executed in " . round(microtime(true) - $startTime, 2) . "s\n");

        // 🎓 Per-Course Analysis
        $courses = $courseLeads->map(function ($course, $index) {
            info("Processing course {$index}: {$course->courseName}\n");

            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb;
            $conversion = $totalLeads > 0 ? number_format(($enrollmentDb / $totalLeads) * 100, 2) : 0;

            // Fetch detailed breakdown by MainSource, OwnerIdName, City
            $aggregates = DB::table('leads')
                ->selectRaw('MainSource, OwnerIdName, mx_City, COUNT(*) as totalLeads, SUM(CASE WHEN ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled')
                ->where('mx_Course_Interested', $course->courseName)
                ->groupBy('MainSource', 'OwnerIdName', 'mx_City')
                ->get();

            // Group by MainSource (Lead Sources)
            // $leadSources = $aggregates->groupBy('MainSource')->map(function ($group, $source) {
            //     $total = $group->sum('totalLeads');
            //     $enrolled = $group->sum('enrolled');
            //     return [
            //         'sourceType' => $source ?? 'Unknown',
            //         'totalLeads' => $total,
            //         'enrollmentLSQ' => $enrolled,
            //         'enrollmentDB' => $enrolled,
            //         'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            //     ];
            // })->values()->toArray();

            $leadSourcesRaw = $aggregates->groupBy('MainSource')->map(function ($group, $source) {
                $total = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');
                return [
                    'sourceType' => $source ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollmentLSQ' => $enrolled,
                    'enrollmentDB' => $enrolled,
                    'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->values()->toArray();

            // --- Separate groups ---
            $facebook = collect($leadSourcesRaw)->firstWhere('sourceType', 'Facebook');
            $googlePaid = collect($leadSourcesRaw)->firstWhere('sourceType', 'Google Paid');
            $googleOrganic = collect($leadSourcesRaw)->firstWhere('sourceType', 'Google Organic');

            // --- Totals ---
            $totalPaidLeads = collect([$facebook, $googlePaid])->sum('totalLeads');
            $totalPaidEnroll = collect([$facebook, $googlePaid])->sum('enrollmentDB');
            $totalPaidConv = $totalPaidLeads > 0 ? round(($totalPaidEnroll / $totalPaidLeads) * 100, 2) : 0;

            $totalOrganicLeads = $googleOrganic['totalLeads'] ?? 0;
            $totalOrganicEnroll = $googleOrganic['enrollmentDB'] ?? 0;
            $totalOrganicConv = $totalOrganicLeads > 0 ? round(($totalOrganicEnroll / $totalOrganicLeads) * 100, 2) : 0;

            // --- Final formatted order ---
            $leadSources = [
                [
                    'sourceType' => 'Total Leads - Paid',
                    'totalLeads' => $totalPaidLeads,
                    'enrollmentLSQ' => $totalPaidEnroll,
                    'enrollmentDB' => $totalPaidEnroll,
                    'conversion' => $totalPaidConv,
                ],
                $facebook,
                $googlePaid,
                $googleOrganic,
                [
                    'sourceType' => 'Total Leads - Organic',
                    'totalLeads' => $totalOrganicLeads,
                    'enrollmentLSQ' => $totalOrganicEnroll,
                    'enrollmentDB' => $totalOrganicEnroll,
                    'conversion' => $totalOrganicConv,
                ],
            ];


            // dd($leadSources);

            // 👥 Team Leads
            $teamLeads = $aggregates->groupBy('OwnerIdName')->map(function ($group, $tl) {
                $totalAssigned = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');
                return [
                    'teamLeadName' => $tl ?? 'N/A',
                    'lcName' => $tl ?? 'N/A',
                    'LeadsAssigned' => $totalAssigned,
                    'enrollmentLSQ' => $enrolled,
                    'actualEnrollmentDB' => $enrolled,
                    'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
                    'totalLeadsAssigned' => $totalAssigned,
                    'totalEnrollmentLSQ' => $enrolled,
                    'totalEnrollmentDB' => $enrolled,
                ];
            })->values()->toArray();

            // 📈 Conversion Analysis (by LC)
            $conversionAnalysis = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) {
                $totalLeads = $group->sum('totalLeads');
                $facebook = $group->where('MainSource', 'Facebook')->sum('totalLeads');
                $googlePaid = $group->where('MainSource', 'Google Paid')->sum('totalLeads');
                $googleOrganic = $group->where('MainSource', 'Google Organic')->sum('totalLeads');
                $facebookConv = $group->where('MainSource', 'Facebook')->sum('enrolled');
                $googlePaidConv = $group->where('MainSource', 'Google Paid')->sum('enrolled');
                $googleOrganicConv = $group->where('MainSource', 'Google Organic')->sum('enrolled');

                $bySource = $group->groupBy('MainSource')->map(fn($s) => [
                    'leads' => $s->sum('totalLeads'),
                    'conversions' => $s->sum('enrolled'),
                    'conversion_rate' => $s->sum('totalLeads') > 0 ? round(($s->sum('enrolled') / $s->sum('totalLeads')) * 100, 2) : 0,
                ]);
                return [
                    'lcName' => $lc ?? 'N/A',
                    'totalLeads' => $totalLeads,
                    'facebook_paid_leads' => $facebook,
                    'google_paid_leads' => $googlePaid,
                    'google_organic_leads' => $googleOrganic,
                    'facebook_paid_conversion' => $facebookConv,
                    'google_conversion' => $googlePaidConv,
                    'google_organic_conversion' => $googleOrganicConv,
                    'sources' => $bySource,
                ];
            })->values()->toArray();

            // 🏙️ Top Cities
            $topCities = $aggregates->groupBy('mx_City')->map(function ($group, $city) {
                $total = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');
                return [
                    'city' => $city ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollments' => $enrolled,
                    'conversion' => $enrolled,
                    'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->sortByDesc('conversion')->take(5)->values()->toArray();

            info("Completed course {$index}: {$course->courseName}\n");

            return [
                'id' => $index + 1,
                'name' => $course->courseName,
                'total_leads' => $totalLeads,
                'enrollment_lsq' => $enrollmentLsq,
                'enrollment_db' => $enrollmentDb,
                'conversion' => $conversion . '%',
                'details' => [
                    'leadSources' => $leadSources,
                    'teamLeads' => $teamLeads,
                    'conversionAnalysis' => $conversionAnalysis,
                    'topCities' => $topCities,
                ],
            ];
        });

        info("🎯 All courses processed in " . round(microtime(true) - $startTime, 2) . "s\n");

        // 🧾 Return Results
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'page' => $page,
                'per_page' => $perPage,
                // 'totalLeadsCount' => $totalLeadsFromLeadTable, // All Leads from Lead Table
                'totalLeadsCount' => $dashboardStats['totalDbLeads'], // All Leads from Lead Table
                'totalConversion' => $dashboardStats['totalConversion'], // All Enrolled Leads from Lead Table
                'totalSalesDBLeads' => $totalSalesDBLeads,
                'totalDbLeads' => $dashboardStats['totalDbLeads'],
                'salesUsers' => $salesUsers->values(),
                'courses' => $courses->take(1000)->values(),
            ]);
        }

        // 🖥️ For non-AJAX requests
        return view('config.config-report');
    }

    public function showDashboard(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $startTime = microtime(true);
        $mode = $request->query('mode');

        // 🟢 Handle non-AJAX (initial page load)
        if (!$request->ajax() && !$request->wantsJson() && !$mode) {
            return view('config.config-report');
        }

        // 🔵 Log every call for debugging
        Log::info('showDashboard called', [
            'mode' => $mode,
            'ajax' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ]);

        // Handle mode-based auxiliary actions
        if ($mode === 'courseDetails') {
            return $this->showCourseDetails($request);
        }
        if ($mode === 'status') {
            $isProcessing = Cache::get('dashboard_processing', false);
            return response()->json(['isProcessing' => $isProcessing]);
        }

        // 🧩 Base Queries
        $salesQuery = DB::connection('mysql_secondary')->table('sales')->where('status', 1);
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->whereIn('status', ['verified', 'un-verified']);

        // 🔍 Filters
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 1000);

        // 🕒 Apply date filters dynamically
        $applyDateFilter = function ($query) use ($dateRange, $fromDate, $toDate) {
            if ($dateRange) {
                switch ($dateRange) {
                    case 'yesterday':
                        $query->whereDate('created_at', now()->subDay()->toDateString());
                        break;
                    case 'weekly':
                        $query->where('created_at', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('created_at', '>=', now()->subDays(30));
                        break;
                    case 'yearly':
                        $query->where('created_at', '>=', now()->subYear());
                        break;
                }
            } elseif ($fromDate && $toDate) {
                $query->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59']);
            } else {
                $query->where('created_at', '>=', now()->subMonths(6));
            }
        };

        $applyDateFilter($salesQuery);
        $applyDateFilter($directPaymentQuery);

        if ($location) {
            $salesQuery->where('location', $location);
            $directPaymentQuery->where('location', $location);
        }

        // Total Leads from Leads Table
        $totalLeadsFromLeadTable = DB::table('leads')->where('created_at', '>', '2025-04-01')->count();

        // Paginated Data Fetch
        $sales = $salesQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $directPayments = $directPaymentQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();

        // Process data
        $newSalesArray = $sales->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'email' => $item->email,
            'contact' => $item->contact,
        ])->toArray();

        $newDirectPaymentArray = $directPayments->map(fn($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'email' => $item->email,
            'contact' => $item->contact,
        ])->toArray();

        $salesUsers = collect(array_merge($newSalesArray, $newDirectPaymentArray));

        // 🧩 Extract emails & contacts
        $emails = $salesUsers->pluck('email')->filter()->unique()->toArray();
        $contacts = $salesUsers->pluck('contact')->filter()->unique()->toArray();

        // 🧮 Leads Matching with Sales / Direct Payments
        $totalSalesDBLeads = DB::table('leads')
            ->where(function ($q) use ($emails, $contacts) {
                if ($emails) $q->whereIn('EmailAddress', $emails);
                if ($contacts) $q->orWhereIn('Phone', $contacts);
            })
            ->count();

        // 📊 Cached Global Stats
        $dashboardStats = Cache::remember('dashboard_summary', 600, function () {
            return [
                'totalDbLeads' => DB::table('leads')->where('created_at', '>', '2025-04-01')->count(),
                'totalConversion' => DB::table('leads')
                    ->where('prospectStage', 'Enrolled')
                    ->where('created_at', '>', '2025-04-01')
                    ->count(),
            ];
        });

        // 📘 Cached Course-Wise Lead Stats
        $courseLeads = Cache::remember('dashboard_courses', 600, function () {
            return DB::table('tbl_course_master')
                ->select([
                    'tbl_course_master.id',
                    'tbl_course_master.courseName',
                    DB::raw('COUNT(leads.id) as total_leads'),
                    DB::raw('SUM(CASE WHEN leads.prospectStage = "Enrolled" THEN 1 ELSE 0 END) as enrollment_db'),
                    DB::raw('MAX(leads.updated_at) as last_updated')
                ])
                ->leftJoin('leads', 'leads.mx_Course_Interested', '=', 'tbl_course_master.courseName')
                ->groupBy('tbl_course_master.id', 'tbl_course_master.courseName')
                ->orderBy('tbl_course_master.courseName')
                ->get();
        });

        // -----------------------------
        // NEW: build LC -> TL name mapping
        // -----------------------------
        // Get distinct OwnerIdName values from leads (non-null)
        $ownerNames = DB::table('leads')
            ->whereNotNull('OwnerIdName')
            ->distinct()
            ->pluck('OwnerIdName')
            ->map(fn($v) => trim(strtolower($v)))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $lcToTl = [];
        if (!empty($ownerNames)) {
            // fetch mapping using your reference join
            $mappings = DB::table('tbl_lead_owner_lc_master as tlcr')
                ->select('tlcr.lcName', 'tl.tl_name')
                ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'tlcr.fk_tl')
                // compare lower(trim(lcName)) to provided ownerNames
                ->whereIn(DB::raw('LOWER(TRIM(tlcr.lcName))'), $ownerNames)
                ->get();

            foreach ($mappings as $m) {
                $key = trim(strtolower($m->lcName ?? ''));
                if ($key !== '') {
                    $lcToTl[$key] = $m->tl_name ?? null;
                }
            }
        }
        // -----------------------------
        // end mapping
        // -----------------------------

        info("✅ Aggregate query executed in " . round(microtime(true) - $startTime, 2) . "s\n");

        // 🎓 Per-Course Analysis
        $courses = $courseLeads->map(function ($course, $index) use ($lcToTl) {
            info("Processing course {$index}: {$course->courseName}\n");

            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb;
            $conversion = $totalLeads > 0 ? number_format(($enrollmentDb / $totalLeads) * 100, 2) : 0;

            // Fetch detailed breakdown by MainSource, OwnerIdName, City
            $aggregates = DB::table('leads')
                ->selectRaw('MainSource, OwnerIdName, mx_City, COUNT(*) as totalLeads, SUM(CASE WHEN ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled')
                ->where('mx_Course_Interested', $course->courseName)
                ->groupBy('MainSource', 'OwnerIdName', 'mx_City')
                ->get();

            // $aggregates = DB::table('leads')
            //     ->leftJoin('tbl_course_master as tc', 'tc.courseName', '=', 'leads.mx_Course_Interested')
            //     ->select(
            //         'leads.OwnerIdName',
            //         'leads.mx_Course_Interested',
            //         'tc.courseLocation',
            //         DB::raw('COUNT(leads.ProspectID) as totalLeads'),
            //         DB::raw('SUM(CASE WHEN leads.ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled')
            //     )
            //     ->groupBy('leads.OwnerIdName', 'leads.mx_Course_Interested', 'tc.courseLocation')
            //     ->get();


            $leadSourcesRaw = $aggregates->groupBy('MainSource')->map(function ($group, $source) {
                $total = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');
                return [
                    'sourceType' => $source ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollmentLSQ' => $enrolled,
                    'enrollmentDB' => $enrolled,
                    'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->values()->toArray();

            // --- Separate groups ---
            $facebook = collect($leadSourcesRaw)->firstWhere('sourceType', 'Facebook');
            $googlePaid = collect($leadSourcesRaw)->firstWhere('sourceType', 'Google Paid');
            $googleOrganic = collect($leadSourcesRaw)->firstWhere('sourceType', 'Google Organic');

            // --- Totals ---
            $totalPaidLeads = collect([$facebook, $googlePaid])->sum('totalLeads');
            $totalPaidEnroll = collect([$facebook, $googlePaid])->sum('enrollmentDB');
            $totalPaidConv = $totalPaidLeads > 0 ? round(($totalPaidEnroll / $totalPaidLeads) * 100, 2) : 0;

            $totalOrganicLeads = $googleOrganic['totalLeads'] ?? 0;
            $totalOrganicEnroll = $googleOrganic['enrollmentDB'] ?? 0;
            $totalOrganicConv = $totalOrganicLeads > 0 ? round(($totalOrganicEnroll / $totalOrganicLeads) * 100, 2) : 0;

            // --- Final formatted order ---
            $leadSources = [
                [
                    'sourceType' => 'Total Leads - Paid',
                    'totalLeads' => $totalPaidLeads,
                    'enrollmentLSQ' => $totalPaidEnroll,
                    'enrollmentDB' => $totalPaidEnroll,
                    'conversion' => $totalPaidConv,
                ],
                $facebook,
                $googlePaid,
                [
                    'sourceType' => 'Total Leads - Organic',
                    'totalLeads' => $totalOrganicLeads,
                    'enrollmentLSQ' => $totalOrganicEnroll,
                    'enrollmentDB' => $totalOrganicEnroll,
                    'conversion' => $totalOrganicConv,
                ],
                $googleOrganic,
            ];

            // 👥 Team Leads (use mapping to get TL name from OwnerIdName (LC))
            // $teamLeads = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) use ($lcToTl) {
            //     $totalAssigned = $group->sum('totalLeads');
            //     $enrolled = $group->sum('enrolled');

            //     $lcKey = trim(strtolower($lc ?? ''));
            //     $tlName = $lcToTl[$lcKey] ?? null;

            //     return [
            //         // original LC name
            //         'lcName' => $lc ?? 'N/A',
            //         // resolved TL name (fallback to 'N/A' if not found)
            //         'teamLeadName' => $tlName ?? 'N/A',
            //         'LeadsAssigned' => $totalAssigned,
            //         'enrollmentLSQ' => $enrolled,
            //         'actualEnrollmentDB' => $enrolled,
            //         'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
            //         'totalLeadsAssigned' => $totalAssigned,
            //         'totalEnrollmentLSQ' => $enrolled,
            //         'totalEnrollmentDB' => $enrolled,
            //     ];
            // })->sortBy(fn($item) => $item['teamLeadName'])->values()->toArray();

            // 👥 Team Leads Grouped by TL
            $teamLeadsRaw = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) {
                $totalAssigned = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');

                // 🔗 Find TL name by LC name
                $tlName = DB::table('tbl_lead_owner_lc_master as tlcr')
                    ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'tlcr.fk_tl')
                    ->whereRaw('TRIM(LOWER(tlcr.lcName)) = ?', [trim(strtolower($lc))])
                    ->value('tl.tl_name');

                return [
                    'teamLeadName' => $tlName ?? 'N/A',
                    'lcName' => $lc ?? 'N/A',
                    'LeadsAssigned' => $totalAssigned,
                    'enrollmentLSQ' => $enrolled,
                    'actualEnrollmentDB' => $enrolled,
                    'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
                ];
            })->values();

            // 🧭 Group by TL name (club multiple LCs under one TL)
            $teamLeads = $teamLeadsRaw->groupBy('teamLeadName')->map(function ($group, $tl) {
                return [
                    'teamLeadName' => $tl,
                    'totalLeadsAssigned' => $group->sum('LeadsAssigned'),
                    'totalEnrollmentLSQ' => $group->sum('enrollmentLSQ'),
                    'totalEnrollmentDB' => $group->sum('actualEnrollmentDB'),
                    'tl_performance' => $group->sum('LeadsAssigned') > 0
                        ? round(($group->sum('actualEnrollmentDB') / $group->sum('LeadsAssigned')) * 100, 2)
                        : 0,
                    'lcs' => $group->map(fn($lc) => [
                        'lcName' => $lc['lcName'],
                        'LeadsAssigned' => $lc['LeadsAssigned'],
                        'enrollmentLSQ' => $lc['enrollmentLSQ'],
                        'actualEnrollmentDB' => $lc['actualEnrollmentDB'],
                        'tl_performance' => $lc['tl_performance'],
                    ])->values(),
                ];
            })->sortBy('teamLeadName')->values()->toArray();


            // 📈 Conversion Analysis (by LC but show TL)
            $conversionAnalysis = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) use ($lcToTl) {
                $totalLeads = $group->sum('totalLeads');
                $facebook = $group->where('MainSource', 'Facebook')->sum('totalLeads');
                $googlePaid = $group->where('MainSource', 'Google Paid')->sum('totalLeads');
                $googleOrganic = $group->where('MainSource', 'Google Organic')->sum('totalLeads');
                $facebookConv = $group->where('MainSource', 'Facebook')->sum('enrolled');
                $googlePaidConv = $group->where('MainSource', 'Google Paid')->sum('enrolled');
                $googleOrganicConv = $group->where('MainSource', 'Google Organic')->sum('enrolled');

                $bySource = $group->groupBy('MainSource')->map(fn($s) => [
                    'leads' => $s->sum('totalLeads'),
                    'conversions' => $s->sum('enrolled'),
                    'conversion_rate' => $s->sum('totalLeads') > 0 ? round(($s->sum('enrolled') / $s->sum('totalLeads')) * 100, 2) : 0,
                ]);

                $lcKey = trim(strtolower($lc ?? ''));
                $tlName = $lcToTl[$lcKey] ?? null;

                return [
                    'lcName' => $lc ?? 'N/A',
                    'tlName' => $tlName ?? 'N/A',
                    'totalLeads' => $totalLeads,
                    'facebook_paid_leads' => $facebook,
                    'google_paid_leads' => $googlePaid,
                    'google_organic_leads' => $googleOrganic,
                    'facebook_paid_conversion' => $facebookConv,
                    'google_conversion' => $googlePaidConv,
                    'google_organic_conversion' => $googleOrganicConv,
                    'sources' => $bySource,
                ];
            })->values()->toArray();

            // 🏙️ Top Cities
            $topCities = $aggregates->groupBy('mx_City')->map(function ($group, $city) {
                $total = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');
                return [
                    'city' => $city ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollments' => $enrolled,
                    'conversion' => $enrolled,
                    'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->sortByDesc('conversion')->take(5)->values()->toArray();

            info("Completed course {$index}: {$course->courseName}\n");

            return [
                'id' => $index + 1,
                'name' => $course->courseName,
                'total_leads' => $totalLeads,
                'enrollment_lsq' => $enrollmentLsq,
                'enrollment_db' => $enrollmentDb,
                'conversion' => $conversion . '%',
                'details' => [
                    'leadSources' => $leadSources,
                    'teamLeads' => $teamLeads,
                    'conversionAnalysis' => $conversionAnalysis,
                    'topCities' => $topCities,
                ],
            ];
        });

        info("🎯 All courses processed in " . round(microtime(true) - $startTime, 2) . "s\n");

        // 🧾 Return Results
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'page' => $page,
                'per_page' => $perPage,
                'totalLeadsCount' => $dashboardStats['totalDbLeads'],
                'totalConversion' => $dashboardStats['totalConversion'],
                'totalSalesDBLeads' => $totalSalesDBLeads,
                'totalDbLeads' => $dashboardStats['totalDbLeads'],
                'salesUsers' => $salesUsers->values(),
                'courses' => $courses->take(1000)->values(),
            ]);
        }

        // 🖥️ For non-AJAX requests
        return view('config.config-report');
    }


    /**
     * Return course-level details for a given course name (lazy-loaded by the UI).
     * Query param: course (string) - course name
     */
    public function showCourseDetails(Request $request)
    {
        $courseName = $request->query('course');
        $location = $request->query('location'); // optional location filter

        if (empty($courseName)) {
            return response()->json(['success' => false, 'message' => 'Missing course name'], 400);
        }

        // --------------------------
        // Step 1. Base Aggregated Query
        // --------------------------
        $aggregates = DB::table('leads')
            ->leftJoin('tbl_course_master as tc', 'tc.courseName', '=', 'leads.mx_Course_Interested')
            ->select(
                'leads.MainSource',
                'leads.OwnerIdName',
                'leads.mx_City',
                'tc.courseLocation',
                DB::raw('COUNT(leads.ProspectID) as totalLeads'),
                DB::raw('SUM(CASE WHEN leads.ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled')
            )
            ->where('leads.mx_Course_Interested', $courseName)
            ->when($location, fn($q) => $q->where('tc.courseLocation', $location))
            ->groupBy('leads.MainSource', 'leads.OwnerIdName', 'leads.mx_City', 'tc.courseLocation')
            ->get();

        // --------------------------
        // Step 2. Lead Sources Summary
        // --------------------------
        $leadSources = $aggregates->groupBy('MainSource')->map(function ($group, $source) {
            $total = $group->sum('totalLeads');
            $enrolled = $group->sum('enrolled');
            return [
                'sourceType' => $source ?? 'Unknown',
                'totalLeads' => $total,
                'enrollmentLSQ' => $enrolled,
                'enrollmentDB' => $enrolled,
                'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            ];
        })->values()->toArray();

        // --------------------------
        // Step 3. TL → LC Grouping (Team Leads Performance)
        // --------------------------
        $teamLeads = $aggregates
            ->groupBy('OwnerIdName')
            ->map(function ($group, $lc) {
                $totalAssigned = $group->sum('totalLeads');
                $enrolled = $group->sum('enrolled');

                // 🔗 Get TL name for each LC (OwnerIdName)
                $tlName = DB::table('tbl_lead_owner_lc_master as tlcr')
                    ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'tlcr.fk_tl')
                    ->whereRaw('TRIM(LOWER(tlcr.lcName)) = ?', [trim(strtolower($lc))])
                    ->value('tl.tl_name');

                return [
                    'teamLeadName' => $tlName ?? 'N/A',
                    'lcName' => $lc ?? 'N/A',
                    'LeadsAssigned' => $totalAssigned,
                    'enrollmentLSQ' => $enrolled,
                    'actualEnrollmentDB' => $enrolled,
                    'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
                    'totalLeadsAssigned' => $totalAssigned,
                    'totalEnrollmentLSQ' => $enrolled,
                    'totalEnrollmentDB' => $enrolled,
                ];
            })
            ->groupBy('teamLeadName') // ✅ Group multiple LCs under one TL
            ->map(function ($group, $tlName) {
                $lcList = $group->map(fn($lc) => [
                    'lcName' => $lc['lcName'],
                    'LeadsAssigned' => $lc['LeadsAssigned'],
                    'enrollmentLSQ' => $lc['enrollmentLSQ'],
                    'actualEnrollmentDB' => $lc['actualEnrollmentDB'],
                    'tl_performance' => $lc['tl_performance'],
                ])->values();

                return [
                    'teamLeadName' => $tlName,
                    'totalLeadsAssigned' => $group->sum('LeadsAssigned'),
                    'totalEnrollmentLSQ' => $group->sum('enrollmentLSQ'),
                    'totalEnrollmentDB' => $group->sum('actualEnrollmentDB'),
                    'tl_performance' => $group->sum('LeadsAssigned') > 0
                        ? round(($group->sum('actualEnrollmentDB') / $group->sum('LeadsAssigned')) * 100, 2)
                        : 0,
                    'lcs' => $lcList,
                ];
            })
            ->sortBy(fn($item) => $item['teamLeadName'])
            ->values()
            ->toArray();

        // --------------------------
        // Step 4. Conversion Analysis (per LC with per-source breakdown)
        // --------------------------
        $conversionAnalysis = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) {
            $totalLeads = $group->sum('totalLeads');
            $bySource = $group->groupBy('MainSource')->map(function ($s) {
                $leads = $s->sum('totalLeads');
                $conversions = $s->sum('enrolled');
                return [
                    'leads' => $leads,
                    'conversions' => $conversions,
                    'conversion_rate' => $leads > 0 ? round(($conversions / $leads) * 100, 2) : 0,
                ];
            })->toArray();

            return [
                'lcName' => $lc ?? 'N/A',
                'totalLeads' => $totalLeads,
                'sources' => $bySource,
            ];
        })->values()->toArray();

        // --------------------------
        // Step 5. Top Cities
        // --------------------------
        $topCities = $aggregates->groupBy('mx_City')->map(function ($group, $city) {
            $total = $group->sum('totalLeads');
            $enrolled = $group->sum('enrolled');
            return [
                'city' => $city ?? 'Unknown',
                'totalLeads' => $total,
                'enrollments' => $enrolled,
                'conversion' => $enrolled,
                'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            ];
        })->sortByDesc('conversion')->take(5)->values()->toArray();

        // --------------------------
        // Step 6. Response
        // --------------------------
        $details = [
            'leadSources' => $leadSources,
            'teamLeads' => $teamLeads,
            'conversionAnalysis' => $conversionAnalysis,
            'topCities' => $topCities,
            'duration' => '12 months',
        ];

        return response()->json(['success' => true, 'details' => $details]);
    }


    public function showCourseDetails_old(Request $request)
    {
        $courseName = $request->query('course');
        if (empty($courseName)) {
            return response()->json(['success' => false, 'message' => 'Missing course name'], 400);
        }

        // Use an aggregated query (same shape as showDashboard's per-course aggregates)
        $aggregates = DB::table('leads')
            ->selectRaw('MainSource as MainSource, OwnerIdName as OwnerIdName, mx_City as mx_City, COUNT(*) as totalLeads, SUM(CASE WHEN ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled')
            ->where('mx_Course_Interested', $courseName)
            ->groupBy('MainSource', 'OwnerIdName', 'mx_City')
            ->get();

        // Build leadSources (grouped by MainSource)
        $leadSources = $aggregates->groupBy('MainSource')->map(function ($group, $source) {
            $total = $group->sum('totalLeads');
            $enrolled = $group->sum('enrolled');
            return [
                'sourceType' => $source ?? 'Unknown',
                'totalLeads' => $total,
                'enrollmentLSQ' => $enrolled,
                'enrollmentDB' => $enrolled,
                'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            ];
        })->values()->toArray();

        // Build teamLeads (grouped by OwnerIdName)
        $teamLeads = $aggregates->groupBy('OwnerIdName')->map(function ($group, $tl) {
            $totalAssigned = $group->sum('totalLeads');
            $enrolled = $group->sum('enrolled');
            return [
                'teamLeadName' => $tl ?? 'N/A',
                'lcName' => $tl ?? 'N/A',
                'LeadsAssigned' => $totalAssigned,
                'enrollmentLSQ' => $enrolled,
                'actualEnrollmentDB' => $enrolled,
                'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
                'totalLeadsAssigned' => $totalAssigned,
                'totalEnrollmentLSQ' => $enrolled,
                'totalEnrollmentDB' => $enrolled,
            ];
        })->values()->toArray();

        // Conversion analysis grouped by OwnerIdName with per-source breakdown
        $conversionAnalysis = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) {
            $totalLeads = $group->sum('totalLeads');
            $facebook = $group->where('MainSource', 'Facebook')->sum('totalLeads');
            $googlePaid = $group->where('MainSource', 'Google Paid')->sum('totalLeads');
            $googleOrganic = $group->where('MainSource', 'Google Organic')->sum('totalLeads');
            $facebookConv = $group->where('MainSource', 'Facebook')->sum('enrolled');
            $googlePaidConv = $group->where('MainSource', 'Google Paid')->sum('enrolled');
            $googleOrganicConv = $group->where('MainSource', 'Google Organic')->sum('enrolled');

            $bySource = $group->groupBy('MainSource')->map(function ($s) {
                $leads = $s->sum('totalLeads');
                $conversions = $s->sum('enrolled');
                return [
                    'leads' => $leads,
                    'conversions' => $conversions,
                    'conversion_rate' => $leads > 0 ? round(($conversions / $leads) * 100, 2) : 0,
                ];
            })->toArray();

            return [
                'lcName' => $lc ?? 'N/A',
                'totalLeads' => $totalLeads,
                'facebook_paid_leads' => $facebook,
                'google_paid_leads' => $googlePaid,
                'google_organic_leads' => $googleOrganic,
                'facebook_paid_conversion' => $facebookConv,
                'google_conversion' => $googlePaidConv,
                'google_organic_conversion' => $googleOrganicConv,
                'sources' => $bySource,
            ];
        })->values()->toArray();

        // Top cities
        $topCities = $aggregates->groupBy('mx_City')->map(function ($group, $city) {
            $total = $group->sum('totalLeads');
            $enrolled = $group->sum('enrolled');
            return [
                'city' => $city ?? 'Unknown',
                'totalLeads' => $total,
                'enrollments' => $enrolled,
                'conversion' => $enrolled,
                'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            ];
        })->sortByDesc('conversion')->take(5)->values()->toArray();

        $details = [
            'leadSources' => $leadSources,
            'teamLeads' => $teamLeads,
            'conversionAnalysis' => $conversionAnalysis,
            'topCities' => $topCities,
            'duration' => '12 months',
        ];

        return response()->json(['success' => true, 'details' => $details]);
    }
}
