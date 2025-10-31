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

    // public function showDashboard(Request $request)
    // {
    //     $dateRange = $request->input('date_range');
    //     $fromDate = $request->input('from_date');
    //     $toDate = $request->input('to_date');
    //     $location = $request->input('location');

    //     // Apply date filters
    //     $applyDateFilter = function ($query) use ($dateRange, $fromDate, $toDate) {
    //         if ($dateRange) {
    //             switch ($dateRange) {
    //                 case 'yesterday':
    //                     $query->whereDate('created_at', now()->subDay()->toDateString());
    //                     break;
    //                 case 'weekly':
    //                     $query->where('created_at', '>=', now()->subDays(7));
    //                     break;
    //                 case 'monthly':
    //                     $query->where('created_at', '>=', now()->subDays(30));
    //                     break;
    //                 case 'yearly':
    //                     $query->where('created_at', '>=', now()->subYear());
    //                     break;
    //             }
    //         } elseif ($fromDate && $toDate) {
    //             $query->whereBetween('created_at', [
    //                 $fromDate . ' 00:00:00',
    //                 $toDate . ' 23:59:59'
    //             ]);
    //         } else {
    //             $query->where('created_at', '>=', now()->subMonths(6));
    //         }
    //     };

    //     $applyDateFilterOnLead = function ($query) use ($dateRange, $fromDate, $toDate) {
    //         if ($dateRange) {
    //             switch ($dateRange) {
    //                 case 'yesterday':
    //                     $query->whereDate('CreatedOn', now()->subDay()->toDateString());
    //                     break;
    //                 case 'weekly':
    //                     $query->where('CreatedOn', '>=', now()->subDays(7));
    //                     break;
    //                 case 'monthly':
    //                     $query->where('CreatedOn', '>=', now()->subDays(30));
    //                     break;
    //                 case 'yearly':
    //                     $query->where('CreatedOn', '>=', now()->subYear());
    //                     break;
    //             }
    //         } elseif ($fromDate && $toDate) {
    //             $query->whereBetween('CreatedOn', [
    //                 $fromDate . ' 00:00:00',
    //                 $toDate . ' 23:59:59'
    //             ]);
    //         } else {
    //             $query->where('CreatedOn', '>=', now()->subMonths(6));
    //         }
    //     };

    //     // Fetch emails & contacts from sales and direct_payments using chunk
    //     $emails = collect();
    //     $contacts = collect();

    //     $salesQuery = DB::connection('mysql_secondary')->table('sales')
    //         ->select('email', 'contact')
    //         ->where('status', 1);

    //     $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
    //         ->select('email', 'contact')
    //         ->whereIn('status', ['verified', 'un-verified']);

    //     $applyDateFilter($salesQuery);
    //     $applyDateFilter($directPaymentQuery);

    //     if ($location) {
    //         $salesQuery->where('location', $location);
    //         $directPaymentQuery->where('location', $location);
    //     }

    //     $salesQuery->orderBy('id')->chunk(1000, function ($chunk) use (&$emails, &$contacts) {
    //         foreach ($chunk as $item) {
    //             if ($item->email) $emails->push($item->email);
    //             if ($item->contact) $contacts->push($item->contact);
    //         }
    //     });

    //     $directPaymentQuery->orderBy('id')->chunk(1000, function ($chunk) use (&$emails, &$contacts) {
    //         foreach ($chunk as $item) {
    //             if ($item->email) $emails->push($item->email);
    //             if ($item->contact) $contacts->push($item->contact);
    //         }
    //     });

    //     $emails = $emails->unique()->values();
    //     $contacts = $contacts->unique()->values();

    //     // Avoid large WHERE IN
    //     $totalSalesDBLeads = 0;
    //     foreach ($emails->chunk(1000) as $emailChunk) {
    //         $totalSalesDBLeads += DB::table('leads')
    //             ->whereIn('EmailAddress', $emailChunk->all())
    //             ->where('created_at', '>', '2025-04-01')
    //             ->count();
    //     }

    //     foreach ($contacts->chunk(1000) as $contactChunk) {
    //         $totalSalesDBLeads += DB::table('leads')
    //             ->whereIn('Phone', $contactChunk->all())
    //             ->where('created_at', '>', '2025-04-01')
    //             ->count();
    //     }

    //     // Course Data
    //     $leadsQuery = DB::table('leads')
    //         ->select(
    //             'mx_Course_Interested',
    //             DB::raw('COUNT(*) as total'),
    //             DB::raw("SUM(CASE WHEN prospectStage = 'Enrolled' THEN 1 ELSE 0 END) as enrolled")
    //         )
    //         ->where('CreatedOn', '>', '2025-04-01')
    //         ->groupBy('mx_Course_Interested')
    //         ->get();


    //     // $applyDateFilterOnLead($leadsQuery);
    //     // $allLeads = $leadsQuery->get();


    //     // $courseGroups = $leadsQuery->groupBy('mx_Course_Interested');
    //     $courseGroups = $leadsQuery;

    //     $totalLeads = 0;
    //     $conversionLSQ = 0;
    //     $conversionDb = 0;
    //     $enrollmentDB = 0;
    //     $courseDetails = [];

    //     foreach ($courseGroups as $courseName => $leads) {
    //         $courseName = $leads->mx_Course_Interested ?? 'N/A';
    //         $totalCourseLeads = $leads->total;
    //         $enrollmentDbCount = $leads->enrolled;
    //         $conversionLSQCount = $enrollmentDbCount;

    //         $totalLeads += $totalCourseLeads;
    //         $conversionDb += $enrollmentDbCount;
    //         $conversionLSQ += $conversionLSQCount;
    //         $enrollmentDB += $enrollmentDbCount;


    //         // $leadSources = $leads->groupBy('lead_source')->map(function ($group, $source) {
    //         //     $total = $group->count();
    //         //     $enrolled = $group->where('prospectStage', 'Enrolled')->count();
    //         //     return [
    //         //         'sourceType' => $source ?? 'Unknown',
    //         //         'totalLeads' => $total,
    //         //         'enrollmentLSQ' => $enrolled,
    //         //         'enrollmentDB' => $enrolled,
    //         //         'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
    //         //     ];
    //         // })->values()->toArray();

    //         $leads = Lead::where('CreatedOn', '>', '2025-04-01');

    //         // Apply location filter if provided
    //         if ($location) {
    //             $leads->where('location', $location);
    //         }

    //         $leads = $leads->get();

    //         $leadSources = $leads->groupBy('Source')->map(function ($group, $source) {
    //             $total = $group->count();
    //             $enrolled = $group->where('ProspectStage', 'Enrolled')->count();

    //             return [
    //                 'sourceType' => $source ?? 'Unknown',
    //                 'totalLeads' => $total,
    //                 'enrollmentLSQ' => $enrolled,
    //                 'enrollmentDB' => $enrolled,
    //                 'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
    //             ];
    //         })->values()->toArray();

    //         $teamLeads = $leads->groupBy('team_lead')->map(function ($group, $tl) {
    //             $totalAssigned = $group->count();
    //             $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
    //             return [
    //                 'teamLeadName' => $tl ?? 'N/A',
    //                 'lcName' => $tl ?? 'N/A',
    //                 'LeadsAssigned' => $totalAssigned,
    //                 'enrollmentLSQ' => $enrolled,
    //                 'actualEnrollmentDB' => $enrolled,
    //                 'tl_performance' => $totalAssigned > 0 ? round(($enrolled / $totalAssigned) * 100, 2) : 0,
    //                 'totalLeadsAssigned' => $totalAssigned,
    //                 'totalEnrollmentLSQ' => $enrolled,
    //                 'totalEnrollmentDB' => $enrolled,
    //             ];
    //         })->values()->toArray();

    //         $conversionAnalysis = $leads->groupBy('team_lead')->map(function ($group, $tl) {
    //             return [
    //                 'lcName' => $tl ?? 'N/A',
    //                 'totalLeads' => $group->count(),
    //                 'facebook_paid_leads' => $group->where('Source', 'Facebook')->count(),
    //                 'google_paid_leads' => $group->where('Source', 'Google')->count(),
    //                 'google_organic_leads' => $group->where('Source', 'Google')->count(),
    //                 'facebook_paid_conversion' => $group->where('Source', 'Facebook')->where('ProspectStage', 'Enrolled')->count(),
    //                 'google_conversion' => $group->where('Source', 'Google')->where('ProspectStage', 'Enrolled')->count(),
    //                 'google_organic_conversion' => $group->where('Source', 'Google')->where('ProspectStage', 'Enrolled')->count(),
    //             ];
    //         })->values()->toArray();

    //         $topCities = $leads->groupBy('city')->map(function ($group, $city) {
    //             $total = $group->count();
    //             $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
    //             return [
    //                 'city' => $city ?? 'Unknown',
    //                 'totalLeads' => $total,
    //                 'enrollments' => $enrolled,
    //                 'conversion' => $enrolled,
    //                 'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
    //             ];
    //         })->sortByDesc('conversion')->take(5)->values()->toArray();

    //         $courseDetails[] = [
    //             'courseName' => $courseName,
    //             'totalLeads' => $totalCourseLeads,
    //             'enrollmentLSQ' => $conversionLSQCount,
    //             'enrollmentDB' => $enrollmentDbCount,
    //             'conversion' => $totalCourseLeads > 0 ? round(($enrollmentDbCount / $totalCourseLeads) * 100, 2) : 0,
    //             'leadSource' => $leadSources,
    //             'team_lead_performance' => $teamLeads,
    //             'conversion_analysis' => $conversionAnalysis,
    //             'top_5_cities' => $topCities
    //         ];
    //     }

    //     return response()->json([
    //         'totalLeads' => $totalLeads,
    //         'conversionLSQ' => $conversionLSQ,
    //         'conversionDb' => $conversionDb,
    //         'enrollmentDB' => $enrollmentDB,
    //         'salesDBLeads' => $totalSalesDBLeads,
    //         'course_details' => $courseDetails
    //     ]);
    // }


    public function showDashboard_working(Request $request)
    {
        // Base query for sales
        $salesQuery = DB::connection('mysql_secondary')->table('sales')
            ->where('status', 1);

        // Base query for direct payments
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->whereIn('status', ['verified', 'un-verified']);

        // Apply date filters if provided
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');

        $applyDateFilter = function ($query) use ($dateRange, $fromDate, $toDate) {
            if ($dateRange) {
                $now = now();
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
                $query->whereBetween('created_at', [
                    $fromDate . ' 00:00:00',
                    $toDate . ' 23:59:59'
                ]);
            } else {
                // Default to last 6 months if no date filter
                $query->where('created_at', '>=', now()->subMonths(6));
            }
        };

        // Apply date filters to both queries
        $applyDateFilter($salesQuery);
        $applyDateFilter($directPaymentQuery);

        // Apply location filter if provided
        if ($location) {
            $salesQuery->where('location', $location);
            $directPaymentQuery->where('location', $location);
        }

        // Execute the queries and get the results
        $sales = $salesQuery->get();
        $directPayments = $directPaymentQuery->get();

        // Process the results
        $newSalesArray = $sales->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'contact' => $item->contact,
            ];
        })->toArray();

        $newDirectPaymentArray = $directPayments->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'contact' => $item->contact,
            ];
        })->toArray();

        $salesUsers = collect(array_merge($newSalesArray, $newDirectPaymentArray));

        // Get counts based on filtered data
        $emails = $salesUsers->pluck('email')->toArray();
        $contacts = $salesUsers->pluck('contact')->toArray();

        $totalSalesDBLeads = DB::table('leads')
            ->whereIn('EmailAddress', $emails)
            ->orWhereIn('Phone', $contacts)
            ->where('created_at', '>', '2025-04-01')
            ->count();

        // Get other required data
        $totalDbLeads = DB::table('leads')->count();
        $totalConversion = DB::table('leads')
            ->where('prospectStage', 'Enrolled')
            ->where('created_at', '>', '2025-04-01')
            ->count();

        // Get courses for the view
        $courseLeads = DB::table('tbl_course_master')
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

        $applyDateFilter($courseLeads);

        // Build courses array with required structure
        $courses = $courseLeads->map(function ($course, $index) {
            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb; // Assuming same as enrollment_db unless you have different data

            $conversion = $totalLeads > 0 ? number_format(($enrollmentLsq / $totalLeads) * 100, 2) : 0;

            return [
                'id' => $index + 1,
                'name' => $course->courseName,
                'total_leads' => $totalLeads,
                'enrollment_lsq' => $enrollmentLsq,
                'enrollment_db' => $enrollmentDb,
                'conversion' => $conversion . '%',
                'details' => [
                    'description' => 'Comprehensive course covering all aspects of ' . $course->courseName,
                    'duration' => '12 months', // Default value, adjust as needed
                ]
            ];
        });

        return view('config.config-report', compact(
            'courses',
            'totalDbLeads',
            'totalConversion',
            'totalSalesDBLeads',
            'salesUsers'
        ));
    }

    public function showDashboard_29Oct_old(Request $request)
    {
        // Base query for sales
        $salesQuery = DB::connection('mysql_secondary')->table('sales')
            ->where('status', 1);

        // Base query for direct payments
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->whereIn('status', ['verified', 'un-verified']);

        // Apply date filters if provided
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');

        $applyDateFilter = function ($query) use ($dateRange, $fromDate, $toDate) {
            if ($dateRange) {
                $now = now();
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
                $query->whereBetween('created_at', [
                    $fromDate . ' 00:00:00',
                    $toDate . ' 23:59:59'
                ]);
            } else {
                // Default to last 6 months if no date filter
                $query->where('created_at', '>=', now()->subMonths(6));
            }
        };

        // Apply date filters to both queries
        $applyDateFilter($salesQuery);
        $applyDateFilter($directPaymentQuery);

        // Apply location filter if provided
        if ($location) {
            $salesQuery->where('location', $location);
            $directPaymentQuery->where('location', $location);
        }

        // Execute the queries and get the results
        $sales = $salesQuery->get();
        $directPayments = $directPaymentQuery->get();

        // Process the results
        $newSalesArray = $sales->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'contact' => $item->contact,
            ];
        })->toArray();

        $newDirectPaymentArray = $directPayments->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'contact' => $item->contact,
            ];
        })->toArray();

        $salesUsers = collect(array_merge($newSalesArray, $newDirectPaymentArray));

        // Get counts based on filtered data
        $emails = $salesUsers->pluck('email')->toArray();
        $contacts = $salesUsers->pluck('contact')->toArray();

        $totalSalesDBLeads = DB::table('leads')
            ->whereIn('EmailAddress', $emails)
            ->orWhereIn('Phone', $contacts)
            ->where('created_at', '>', '2025-04-01')
            ->count();

        // Get other required data
        $totalDbLeads = DB::table('leads')->count();
        $totalConversion = DB::table('leads')
            ->where('prospectStage', 'Enrolled')
            ->where('created_at', '>', '2025-04-01')
            ->count();

        // Get courses for the view
        $courseLeads = DB::table('tbl_course_master')
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
            ->limit(1000)
            // ->offset(0)
            ->get();

        $applyDateFilter($courseLeads);

        // Build courses array with required structure
        $courses = $courseLeads->map(function ($course, $index) {
            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb; // Assuming same as enrollment_db unless you have different data

            $conversion = $totalLeads > 0 ? number_format(($enrollmentLsq / $totalLeads) * 100, 2) : 0;

            // First, fetch the leads for the current course
            $leads = DB::table('leads')
                ->where('mx_Course_Interested', $course->courseName)
                ->get();
            // ->groupBy('Source');

            $leadSources = $leads->groupBy('Source')->map(function ($group, $source) {
                $total = $group->count();
                $enrolled = $group->where('ProspectStage', 'Enrolled')->count();

                return [
                    'sourceType' => $source ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollmentLSQ' => $enrolled,
                    'enrollmentDB' => $enrolled,
                    'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->values()->toArray();

            $teamLeads = $leads->groupBy('OwnerIdName')->map(function ($group, $tl) {
                $totalAssigned = $group->count();
                $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
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

            $conversionAnalysis = $leads->groupBy('OwnerIdName')->map(function ($group, $lc) {
                return [
                    'lcName' => $lc ?? 'N/A',
                    'totalLeads' => $group->count(),
                    'facebook_paid_leads' => $group->where('Source', 'Facebook')->count(),
                    'google_paid_leads' => $group->where('Source', 'Google')->count(),
                    'google_organic_leads' => $group->where('Source', 'Google')->count(),
                    'facebook_paid_conversion' => $group->where('Source', 'Facebook')->where('ProspectStage', 'Enrolled')->count(),
                    'google_conversion' => $group->where('Source', 'Google')->where('ProspectStage', 'Enrolled')->count(),
                    'google_organic_conversion' => $group->where('Source', 'Google')->where('ProspectStage', 'Enrolled')->count(),
                ];
            })->values()->toArray();

            $topCities = $leads->groupBy('mx_City')->map(function ($group, $city) {
                $total = $group->count();
                $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
                return [
                    'city' => $city ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollments' => $enrolled,
                    'conversion' => $enrolled,
                    'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->sortByDesc('conversion')->take(5)->values()->toArray();

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
                    'duration' => '12 months',
                ]
            ];
        });

        // dd($courses);

        // return view('config.config-report', compact(
        //     'totalDbLeads',
        //     'totalConversion',
        //     'totalSalesDBLeads',
        //     'salesUsers',
        //     'courses'
        // ));
        return response()->json([
            'totalDbLeads' => $totalDbLeads,
            'totalConversion' => $totalConversion,
            'totalSalesDBLeads' => $totalSalesDBLeads,
            'salesUsers' => $salesUsers->values(),
            'courses' => $courses->values(),
        ]);
    }
    
    public function showDashboard(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $startTime = microtime(true);
        // If this is a normal browser GET (no AJAX, no mode), return the view immediately
        $mode = $request->query('mode');
        if (!$request->ajax() && !$request->wantsJson() && !$mode) {
            return view('config.config-report');
        }

        // Log entry for debugging duplicate calls (includes mode and whether request is AJAX)
        Log::info('showDashboard called', [
            'mode' => $mode,
            'ajax' => $request->ajax(),
            'wantsJson' => $request->wantsJson(),
            'ip' => $request->ip(),
            'ua' => $request->header('User-Agent'),
        ]);

    // Allow auxiliary JSON actions via the same route when called with ?mode=...
        if ($mode === 'courseDetails') {
            return $this->showCourseDetails($request);
        }
        if ($mode === 'status') {
            $isProcessing = Cache::get('dashboard_processing', false);
            return response()->json(['isProcessing' => $isProcessing]);
        }
        
        // dd('TEst Akash');
        // Base queries
        $salesQuery = DB::connection('mysql_secondary')->table('sales')->where('status', 1)->limit(1000);
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
        ->whereIn('status', ['verified', 'un-verified'])->limit(1000);
        
        // Filters
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 1000);
        
        // Apply date range filter
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

        // ✅ Paginated fetch instead of full table load
        $sales = $salesQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $directPayments = $directPaymentQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();

        // dd($directPayments);
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
        // dd($salesUsers);

        // Extract emails & contacts
        $emails = $salesUsers->pluck('email')->filter()->unique()->toArray();
        $contacts = $salesUsers->pluck('contact')->filter()->unique()->toArray();

        // ✅ Optimized lead lookup
        // $totalSalesDBLeads = DB::table('leads')
        //     ->where(function ($q) use ($emails, $contacts) {
        //         if ($emails) $q->whereIn('EmailAddress', $emails);
        //         if ($contacts) $q->orWhereIn('Phone', $contacts);
        //     })
        //     ->where('created_at', '>', '2025-04-01')
        //     ->limit(1000)
        //     ->count();

        $totalSalesDBLeads = DB::table('leads')
            ->where(function ($q) use ($emails, $contacts) {
                if ($emails) {
                    $q->whereIn('EmailAddress', $emails);
                }
                if ($contacts) {
                    $q->whereIn('Phone', $contacts);
                }
            })
            ->take(1000) // alias for limit()
            ->get()
            ->count();

        // dd($totalSalesDBLeads);
        // Cached global stats
        $dashboardStats = Cache::remember('dashboard_summary', 600, function () {
            return [
                'totalDbLeads' => DB::table('leads')->count(),
                'totalConversion' => DB::table('leads')
                    ->where('prospectStage', 'Enrolled')
                    ->where('created_at', '>', '2025-04-01')
                    ->count(),
            ];
        });

        // dd($dashboardStats);
        // ✅ Course leads with caching
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
                ->limit(1000)
                ->orderBy('tbl_course_master.courseName')
                ->get();
        });

        info( "✅ Aggregate query executed in " . round(microtime(true) - $startTime, 2) . "s\n");

        info( "Starting dashboard generation...\n");

        $courses = $courseLeads->map(function ($course, $index) {
            info( "Processing course: {$course->courseName}\n");

            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb;
            $conversion = $totalLeads > 0 ? number_format(($enrollmentLsq / $totalLeads) * 100, 2) : 0;

            // 🔹 Only last 1000 leads per course — include all necessary fields
            $subQuery = DB::table('leads')
                ->select('id', 'Source', 'OwnerIdName', 'mx_City', 'ProspectStage')
                ->where('mx_Course_Interested', $course->courseName)
                ->orderByDesc('id')
                ->limit(1000);

            // 🔹 Fetch all aggregations in one go
            $aggregates = DB::table(DB::raw("({$subQuery->toSql()}) as leads"))
                ->mergeBindings($subQuery)
                ->selectRaw('
            Source,
            OwnerIdName,
            mx_City,
            COUNT(*) as totalLeads,
            SUM(CASE WHEN ProspectStage = "Enrolled" THEN 1 ELSE 0 END) as enrolled
        ')
                ->groupBy('Source', 'OwnerIdName', 'mx_City')
                ->get();

            // 🔹 Lead Sources
            $leadSources = $aggregates->groupBy('Source')->map(function ($group, $source) {
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

            // 🔹 Team Leads
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

            // 🔹 Conversion Analysis
            $conversionAnalysis = $aggregates->groupBy('OwnerIdName')->map(function ($group, $lc) {
                $totalLeads = $group->sum('totalLeads');
                $facebook = $group->where('Source', 'Facebook')->sum('totalLeads');
                $google = $group->where('Source', 'Google')->sum('totalLeads');
                $facebookConv = $group->where('Source', 'Facebook')->sum('enrolled');
                $googleConv = $group->where('Source', 'Google')->sum('enrolled');
                return [
                    'lcName' => $lc ?? 'N/A',
                    'totalLeads' => $totalLeads,
                    'facebook_paid_leads' => $facebook,
                    'google_paid_leads' => $google,
                    'google_organic_leads' => $google,
                    'facebook_paid_conversion' => $facebookConv,
                    'google_conversion' => $googleConv,
                    'google_organic_conversion' => $googleConv,
                ];
            })->values()->toArray();

            // 🔹 Top Cities
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

            info( "Completed course: {$course->courseName}\n");

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
                    'duration' => '12 months',
                ],
            ];
        });


        info( "🎯 All courses processed in " . round(microtime(true) - $startTime, 2) . "s\n");

        // dd($courses);

        if ($request->ajax() || $request->wantsJson()) {
            // Return JSON for AJAX requests
            return response()->json([
                'page' => $page,
                'per_page' => $perPage,
                'totalDbLeads' => $dashboardStats['totalDbLeads'],
                'totalConversion' => $dashboardStats['totalConversion'],
                'totalSalesDBLeads' => $totalSalesDBLeads,
                'salesUsers' => $salesUsers->values(),
                'courses' => array_slice($courses->values()->toArray(), 0, 1000), // Limit to last 1000 records
            ]);
        }

        // For non-AJAX requests, return the view
        return view('config.config-report');
    }

    /**
     * JSON endpoint: return dashboard data for AJAX/DataTable
     * This method reuses the same data-building logic as showDashboard but
     * always returns JSON. It also ensures we return up to the last 1000 records.
     */
    public function showDashboardData(Request $request)
    {
        // We'll replicate the data-building portion from showDashboard but return JSON.
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        // Base queries
        $salesQuery = DB::connection('mysql_secondary')->table('sales')->where('status', 1);
        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->whereIn('status', ['verified', 'un-verified']);

        // Filters
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 1000);

        // Apply date range filter closure (same as showDashboard)
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

        // Paginated fetch for external sources
        $sales = $salesQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();
        $directPayments = $directPaymentQuery->offset(($page - 1) * $perPage)->limit($perPage)->get();

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

        // Extract emails & contacts
        $emails = $salesUsers->pluck('email')->filter()->unique()->toArray();
        $contacts = $salesUsers->pluck('contact')->filter()->unique()->toArray();

        // Optimized lead lookup for sales DB leads
        $totalSalesDBLeads = DB::table('leads')
            ->where(function ($q) use ($emails, $contacts) {
                if ($emails) $q->whereIn('EmailAddress', $emails);
                if ($contacts) $q->orWhereIn('Phone', $contacts);
            })
            ->where('created_at', '>', '2025-04-01')
            ->count();

        // Cached global stats
        $dashboardStats = Cache::remember('dashboard_summary', 600, function () {
            return [
                'totalDbLeads' => DB::table('leads')->count(),
                'totalConversion' => DB::table('leads')
                    ->where('prospectStage', 'Enrolled')
                    ->where('created_at', '>', '2025-04-01')
                    ->count(),
            ];
        });

        // Course leads with caching
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
                ->limit(1000)
                ->get();
        });

        // Build the course array; keep last_updated for ordering
        $courses = $courseLeads->map(function ($course, $index) {
            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $enrollmentLsq = $enrollmentDb;
            $conversion = $totalLeads > 0 ? number_format(($enrollmentLsq / $totalLeads) * 100, 2) : 0;

            return [
                'id' => $index + 1,
                'name' => $course->courseName,
                'total_leads' => $totalLeads,
                'enrollment_lsq' => $enrollmentLsq,
                'enrollment_db' => $enrollmentDb,
                'conversion' => $conversion . '%',
                'last_updated' => $course->last_updated ?? null,
                'details' => [
                    'leadSources' => [],
                    'teamLeads' => [],
                    'conversionAnalysis' => [],
                    'topCities' => [],
                    'duration' => '12 months',
                ],
            ];
        })->values()->toArray();

        // Order by last_updated desc to get 'last' records, then slice up to 1000
        usort($courses, function ($a, $b) {
            $ta = $a['last_updated'] ? strtotime($a['last_updated']) : 0;
            $tb = $b['last_updated'] ? strtotime($b['last_updated']) : 0;
            return $tb <=> $ta;
        });

        $coursesLimited = array_slice($courses, 0, 1000);

        return response()->json([
            'page' => $page,
            'per_page' => $perPage,
            'totalDbLeads' => $dashboardStats['totalDbLeads'],
            'totalConversion' => $dashboardStats['totalConversion'],
            'totalSalesDBLeads' => $totalSalesDBLeads,
            'salesUsers' => $salesUsers->values(),
            'courses' => $coursesLimited,
        ]);
    }

    /**
     * Return course-level details for a given course name (lazy-loaded by the UI).
     * Query param: course (string) - course name
     */
    public function showCourseDetails(Request $request)
    {
        $courseName = $request->query('course');
        if (empty($courseName)) {
            return response()->json(['success' => false, 'message' => 'Missing course name'], 400);
        }

        // Fetch leads for this course. Use a reasonably bounded approach.
        $leads = DB::table('leads')
            ->where('mx_Course_Interested', $courseName)
            ->get();

        // Build leadSources
        $leadSources = collect($leads)->groupBy('Source')->map(function ($group, $source) {
            $total = $group->count();
            $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
            return [
                'sourceType' => $source ?? 'Unknown',
                'totalLeads' => $total,
                'enrollmentLSQ' => $enrolled,
                'enrollmentDB' => $enrolled,
                'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
            ];
        })->values()->toArray();

        // Build teamLeads
        $teamLeads = collect($leads)->groupBy('OwnerIdName')->map(function ($group, $tl) {
            $totalAssigned = $group->count();
            $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
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

        // Top cities
        $topCities = collect($leads)->groupBy('mx_City')->map(function ($group, $city) {
            $total = $group->count();
            $enrolled = $group->where('ProspectStage', 'Enrolled')->count();
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
            'topCities' => $topCities,
            'duration' => '12 months',
        ];

        return response()->json(['success' => true, 'details' => $details]);
    }

    public function showDashboardssssssss(Request $request)
    {
        $dateRange = $request->input('date_range');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');
        $location = $request->input('location');

        // Base query for leads with proper date filtering
        $leadsQuery = DB::table('leads')
            ->select(
                'mx_Course_Interested',
                'Source',
                'ProspectStage',
                'mx_City',
                'OwnerIdName as team_lead'
            );

        // Apply date filter
        if ($dateRange) {
            switch ($dateRange) {
                case 'yesterday':
                    $leadsQuery->whereDate('CreatedOn', now()->subDay()->toDateString());
                    break;
                case 'weekly':
                    $leadsQuery->where('CreatedOn', '>=', now()->subDays(7));
                    break;
                case 'monthly':
                    $leadsQuery->where('CreatedOn', '>=', now()->subDays(30));
                    break;
                case 'yearly':
                    $leadsQuery->where('CreatedOn', '>=', now()->subYear());
                    break;
            }
        } elseif ($fromDate && $toDate) {
            $leadsQuery->whereBetween('CreatedOn', [
                $fromDate . ' 00:00:00',
                $toDate . ' 23:59:59'
            ]);
        } else {
            $leadsQuery->where('CreatedOn', '>=', now()->subMonths(6));
        }

        // Apply location filter if provided
        if ($location) {
            $leadsQuery->where('mx_City', $location);
        }

        $leads = $leadsQuery->get();

        // Group by course
        $courseGroups = $leads->groupBy('mx_Course_Interested');

        $totalDbLeads = $leads->count();
        $totalConversion = $leads->where('ProspectStage', 'Enrolled')->count();

        // ##############################
        // Common date filter closure
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
                $query->whereBetween('created_at', [
                    $fromDate . ' 00:00:00',
                    $toDate . ' 23:59:59'
                ]);
            } else {
                $query->where('created_at', '>=', now()->subMonths(6));
            }
        };

        // Fetch emails & contacts from sales and direct_payments using chunk
        $emails = collect();
        $contacts = collect();

        $salesQuery = DB::connection('mysql_secondary')->table('sales')
            ->select('email', 'contact')
            ->where('status', 1);

        $directPaymentQuery = DB::connection('mysql_secondary')->table('direct_payments')
            ->select('email', 'contact')
            ->whereIn('status', ['verified', 'un-verified']);

        $applyDateFilter($salesQuery);
        $applyDateFilter($directPaymentQuery);

        if ($location) {
            $salesQuery->where('location', $location);
            $directPaymentQuery->where('location', $location);
        }

        $salesQuery->orderBy('id', 'desc')->chunk(1000, function ($chunk) use (&$emails, &$contacts) {
            foreach ($chunk as $item) {
                if ($item->email) $emails->push($item->email);
                if ($item->contact) $contacts->push($item->contact);
            }
        });

        $directPaymentQuery->orderBy('id', 'desc')->chunk(1000, function ($chunk) use (&$emails, &$contacts) {
            foreach ($chunk as $item) {
                if ($item->email) $emails->push($item->email);
                if ($item->contact) $contacts->push($item->contact);
            }
        });

        $emails = $emails->unique()->values();
        $contacts = $contacts->unique()->values();

        // Avoid huge whereIn in one query: break into chunks
        $totalSalesDBLeads = 0;

        foreach ($emails->chunk(1000) as $emailChunk) {
            $totalSalesDBLeads += DB::table('leads')
                ->whereIn('EmailAddress', $emailChunk->all())
                ->where('created_at', '>', '2025-04-01')
                ->count();
        }

        foreach ($contacts->chunk(1000) as $contactChunk) {
            $totalSalesDBLeads += DB::table('leads')
                ->whereIn('Phone', $contactChunk->all())
                ->where('created_at', '>', '2025-04-01')
                ->count();
        }

        // ###################################

        $applyDateFilterOnLead = function ($query) use ($dateRange, $fromDate, $toDate) {
            if ($dateRange) {
                switch ($dateRange) {
                    case 'yesterday':
                        $query->whereDate('CreatedOn', now()->subDay()->toDateString());
                        break;
                    case 'weekly':
                        $query->where('CreatedOn', '>=', now()->subDays(7));
                        break;
                    case 'monthly':
                        $query->where('CreatedOn', '>=', now()->subDays(30));
                        break;
                    case 'yearly':
                        $query->where('CreatedOn', '>=', now()->subYear());
                        break;
                }
            } elseif ($fromDate && $toDate) {
                $query->whereBetween('CreatedOn', [
                    $fromDate . ' 00:00:00',
                    $toDate . ' 23:59:59'
                ]);
            } else {
                $query->where('CreatedOn', '>=', now()->subMonths(6));
            }
        };

        // Courses with applied filters
        $courseLeadsQuery = DB::table('tbl_course_master')
            ->select([
                'tbl_course_master.id',
                'tbl_course_master.courseName',
                DB::raw('COUNT(leads.id) as total_leads'),
                DB::raw('SUM(CASE WHEN leads.prospectStage = "Enrolled" THEN 1 ELSE 0 END) as enrollment_db'),
                DB::raw('MAX(leads.updated_at) as last_updated')
            ])
            ->leftJoin('leads', 'leads.mx_Course_Interested', '=', 'tbl_course_master.courseName')
            ->groupBy('tbl_course_master.id', 'tbl_course_master.courseName')
            ->orderBy('tbl_course_master.courseName');

        $applyDateFilterOnLead($courseLeadsQuery);

        $courseLeads = $courseLeadsQuery->get();

        $courses = $courseLeads->map(function ($course, $index) {
            $totalLeads = (int) $course->total_leads;
            $enrollmentDb = (int) $course->enrollment_db;
            $conversion = $totalLeads > 0 ? number_format(($enrollmentDb / $totalLeads) * 100, 2) : 0;

            return [
                'id' => $index + 1,
                'name' => $course->courseName,
                'total_leads' => $totalLeads,
                'enrollment_lsq' => $enrollmentDb,
                'enrollment_db' => $enrollmentDb,
                'conversion' => $conversion . '%',
                'details' => [
                    'description' => 'Comprehensive course covering all aspects of ' . $course->courseName,
                    'duration' => '12 months',
                ]
            ];
        });

        // ##############################

        $courseDetails = $courseGroups->map(function ($leads, $courseName) {
            $totalCourseLeads = $leads->count();
            $enrolled = $leads->where('ProspectStage', 'Enrolled')->count();

            $leadSources = $leads->groupBy('Source')->map(function ($sourceGroup, $source) {
                $total = $sourceGroup->count();
                $enrolled = $sourceGroup->where('ProspectStage', 'Enrolled')->count();

                return [
                    'sourceType' => $source ?? 'Unknown',
                    'totalLeads' => $total,
                    'enrollmentLSQ' => $enrolled,
                    'enrollmentDB' => $enrolled,
                    'conversion' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->values();

            $teamLeads = $leads->groupBy('team_lead')->map(function ($tlGroup, $tl) {
                $total = $tlGroup->count();
                $enrolled = $tlGroup->where('ProspectStage', 'Enrolled')->count();

                return [
                    'teamLeadName' => $tl ?? 'N/A',
                    'lcName' => $tl ?? 'N/A',
                    'LeadsAssigned' => $total,
                    'enrollmentLSQ' => $enrolled,
                    'actualEnrollmentDB' => $enrolled,
                    'tl_performance' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                ];
            })->values();

            $topCities = $leads->groupBy('mx_City')
                ->map(function ($cityGroup, $city) {
                    $total = $cityGroup->count();
                    $enrolled = $cityGroup->where('ProspectStage', 'Enrolled')->count();

                    return [
                        'city' => $city ?? 'Unknown',
                        'totalLeads' => $total,
                        'enrollments' => $enrolled,
                        'conversion' => $enrolled,
                        'conversion_rate' => $total > 0 ? round(($enrolled / $total) * 100, 2) : 0,
                    ];
                })
                ->sortByDesc('conversion')
                ->take(5)
                ->values();

            return [
                'courseName' => $courseName ?? 'N/A',
                'totalLeads' => $totalCourseLeads,
                'enrollmentLSQ' => $enrolled,
                'enrollmentDB' => $enrolled,
                'totalConversion' => $totalCourseLeads > 0 ? round(($enrolled / $totalCourseLeads) * 100, 2) : 0,
                'leadSource' => $leadSources,
                'team_lead_performance' => $teamLeads,
                'top_5_cities' => $topCities
            ];
        })->values();

        // $courses[] = ([
        //     'totalLeads' => $totalLeads,
        //     'conversionLSQ' => $enrolledCount ?? 0,
        //     'conversionDb' => $enrolledCount ?? 0,
        //     'enrollmentDB' => $enrolledCount ?? 0,
        //     'course_details' => $courseDetails
        // ]);

        return view('config.config-report', compact('totalDbLeads', 'totalConversion', 'totalSalesDBLeads', 'courses'));
    }
}
