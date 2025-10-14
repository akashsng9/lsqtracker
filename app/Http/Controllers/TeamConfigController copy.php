<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 0); // unlimited


use App\Jobs\FetchLeadsJob;
use App\Models\LeadSource;
use App\Models\TeamConfig;
use App\Models\TLMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeamConfigController extends Controller
{
    /**
{{ ... }}
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

        $teamLead = TLMaster::where('status','1')
            ->select('id','tl_name')
            ->get();
            
        $leadType = LeadSource::where('status','1')
            ->select('sourceType')
            ->distinct()
            ->get();
            
        // Get initial data (first page, no filters)
        $leads = DB::table('leads')
            ->select([
                'leads.*',
                'tbl_lead_owner_lc_master.lcName',
                'tbl_tl_master.tl_name',
                'tbl_course_master.courseName',
                'tbl_lead_source_master.sourceType as lead_source_type'
            ])
            ->leftJoin('tbl_lead_owner_lc_master', 'leads.OwnerIdName', '=', 'tbl_lead_owner_lc_master.lcName')
            ->leftJoin('tbl_tl_master', 'tbl_lead_owner_lc_master.fk_tl', '=', 'tbl_tl_master.id')
            ->leftJoin('tbl_course_master', 'leads.mx_Lead_Course', '=', 'tbl_course_master.courseName')
            ->leftJoin('tbl_lead_source_master', 'leads.Source', '=', 'tbl_lead_source_master.leadSource')
            ->orderBy('leads.CreatedOn', 'desc')
            ->paginate(25);
            
        return view('config.team-mumbai', compact('courses', 'lcs', 'leads', 'teamLead', 'leadType'));
    }

    public function filterMumbai(Request $request)
    {
        // Start building the query
        $query = DB::table('leads')
            ->select([
                'leads.*',
                'tbl_lead_owner_lc_master.lcName',
                'tbl_tl_master.tl_name',
                'tbl_course_master.courseName',
                'tbl_lead_source_master.sourceType as lead_source_type'
            ])
            ->leftJoin('tbl_lead_owner_lc_master', 'leads.OwnerIdName', '=', 'tbl_lead_owner_lc_master.lcName')
            ->leftJoin('tbl_tl_master', 'tbl_lead_owner_lc_master.fk_tl', '=', 'tbl_tl_master.id')
            ->leftJoin('tbl_course_master', 'leads.mx_Lead_Course', '=', 'tbl_course_master.courseName')
            ->leftJoin('tbl_lead_source_master', 'leads.Source', '=', 'tbl_lead_source_master.leadSource');

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

        // Execute the query with pagination
        $leads = $query->orderBy('leads.CreatedOn', 'desc')
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

        $teamLead = TLMaster::where('status','1')
            ->select('id','tl_name')
            ->get();
            
        $leadType = LeadSource::where('status','1')
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

        $teamLead = TLMaster::where('status','1')
            ->select('id','tl_name')
            ->get();

        $leadType = LeadSource::where('status','1')
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
            ->select('tbl_lead_owner_lc_master.id', 'tbl_lead_owner_lc_master.lcName', 'tbl_lead_owner_lc_master.location', 'tbl_lead_owner_lc_master.status', 'tbl_lead_owner_lc_master.updatedAt', DB::raw('tl.tl_name as tl_name'), 'tbl_lead_owner_lc_master.fk_tl')
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
        \Log::info('Data being passed to lc-index view:', $data);
        
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
            'createdAt'=> now(),
            'updatedAt'=> now(),
            'deletedBy'=> null,
            'updatedBy'=> null,
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
}
