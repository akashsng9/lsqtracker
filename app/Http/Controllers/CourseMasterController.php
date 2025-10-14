<?php

namespace App\Http\Controllers;

use App\Models\CourseMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseMasterController extends Controller
{
    public function index(Request $request)
    {
        $selectedLocation = $request->query('location');

        // Build dynamic locations list from courses and LCs
        $courseLocations = DB::table('tbl_course_master')
            ->select('courseLocation')
            ->whereNotNull('courseLocation')
            ->groupBy('courseLocation')
            ->pluck('courseLocation')
            ->toArray();

        $lcLocations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $locations = collect(array_unique(array_filter(array_merge($courseLocations, $lcLocations))))
            ->sort()->values();

        $query = CourseMaster::query();
        if (!empty($selectedLocation)) {
            $query->where('courseLocation', $selectedLocation);
        }
        $courses = $query->orderBy('courseLocation')->orderBy('courseName')->get();

        // Get all course IDs for the current query
        $courseIds = $courses->pluck('id')->toArray();

        // Initialize all counts to 0 first
        $counts = array_fill_keys($courseIds, 0);

        // Get actual counts from the database
        $mappedCounts = DB::table('tbl_lc_course_master')
            ->select('fk_course', DB::raw('COUNT(*) as cnt'))
            ->whereIn('fk_course', $courseIds)
            ->groupBy('fk_course')
            ->pluck('cnt', 'fk_course')
            ->toArray();

        // Merge the counts, preserving 0 for courses with no mappings
        $counts = array_merge($counts, $mappedCounts);

        return view('course.index', [
            'courses' => $courses,
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'mappedCounts' => $counts,
        ]);
    }

    public function create()
    {
        // Locations for select
        $courseLocations = DB::table('tbl_course_master')
            ->select('courseLocation')
            ->whereNotNull('courseLocation')
            ->groupBy('courseLocation')
            ->pluck('courseLocation')
            ->toArray();

        $lcLocations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $locations = collect(array_unique(array_filter(array_merge($courseLocations, $lcLocations))))
            ->sort()->values();

        return view('course.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'courseName'     => 'required|string|max:255',
            'CourseId'       => 'nullable|string|max:100',
            'courseLocation' => 'required|string|max:255',
            'CourseStatus'   => 'required|in:0,1',
            'keyword'        => 'nullable|string|max:255',
        ]);
        $validated['CourseStatus'] = (int)$validated['CourseStatus'];
        CourseMaster::create($validated);
        return redirect()->route('configuration.course.index')->with('success', 'Course created successfully');
    }

    public function edit(CourseMaster $course)
    {
        $courseLocations = DB::table('tbl_course_master')
            ->select('courseLocation')
            ->whereNotNull('courseLocation')
            ->groupBy('courseLocation')
            ->pluck('courseLocation')
            ->toArray();

        $lcLocations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $locations = collect(array_unique(array_filter(array_merge($courseLocations, $lcLocations))))
            ->sort()->values();

        return view('course.edit', compact('course', 'locations'));
    }

    public function update(Request $request, CourseMaster $course)
    {
        $validated = $request->validate([
            'courseName'     => 'required|string|max:255',
            'CourseId'       => 'nullable|string|max:100',
            'courseLocation' => 'required|string|max:255',
            'CourseStatus'   => 'required|in:0,1',
            'keyword'        => 'nullable|string|max:255',
        ]);
        $validated['CourseStatus'] = (int)$validated['CourseStatus'];
        $course->update($validated);
        return redirect()->route('configuration.course.index')->with('success', 'Course updated successfully');
    }

    public function destroy(CourseMaster $course)
    {
        $course->delete();
        return redirect()->route('configuration.course.index')->with('success', 'Course deleted successfully');
    }

    // Show mapping form to associate LCs to a Course
    public function mapLcs(Request $request, CourseMaster $course)
    {
        $selectedLocation = $request->query('location');

        // Build locations for filtering LCs
        $locations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('location')
            ->pluck('location');

        $lcQuery = DB::table('tbl_lead_owner_lc_master as lc')
            ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'lc.fk_tl')
            ->whereNull('tl.deleted_at')
            ->select('lc.id', 'lc.lcName', 'lc.location', 'lc.status');

        if (!empty($selectedLocation)) {
            $lcQuery->where('lc.location', $selectedLocation);
        }

        // Fetch all LCs (active ones first)
        $lcs = $lcQuery->orderByDesc('lc.status')->orderBy('lc.lcName')->get();

        // Fetch existing mapping for this course
        $mappedLcIds = DB::table('tbl_lc_course_master')
            ->where('fk_course', $course->id)
            ->pluck('fk_lc')
            ->toArray();

        return view('course.map-lcs', [
            'course' => $course,
            'lcs' => $lcs,
            'locations' => $locations,
            'selectedLocation' => $selectedLocation,
            'mappedLcIds' => $mappedLcIds,
        ]);
    }

    // Save mapping
    public function saveMapLcs(Request $request, CourseMaster $course)
    {
        $validated = $request->validate([
            'lc_ids' => 'array',
            'lc_ids.*' => 'integer|exists:tbl_lead_owner_lc_master,id',
        ]);

        $lcIds = collect($validated['lc_ids'] ?? [])->unique()->values()->all();

        DB::transaction(function () use ($course, $lcIds) {
            // Remove existing mappings for this course
            DB::table('tbl_lc_course_master')->where('fk_course', $course->id)->delete();

            // Insert new mappings
            $now = now();
            $rows = [];
            foreach ($lcIds as $lcId) {
                $rows[] = [
                    'fk_lc' => (int)$lcId,
                    'fk_course' => (int)$course->id,
                    'created_on' => $now,
                    'updated_on' => $now,
                    'updated_by' => null,
                ];
            }
            if (!empty($rows)) {
                DB::table('tbl_lc_course_master')->insert($rows);
            }
        });

        return redirect()->route('configuration.course.index', ['location' => $course->courseLocation])
            ->with('success', 'LC mapping updated for course: ' . $course->courseName);
    }

    // Assigned Courses view: show which courses are mapped to which LCs
    public function assignedCourses(Request $request)
    {
        $selectedLocation = $request->query('location');
        $selectedCourse = $request->query('course');

        // Build locations for filter
        $locations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->orderBy('location')
            ->pluck('location');

        // Get unique courses for filter
        $courses = DB::table('tbl_course_master')
            ->select('id', 'courseName')
            ->orderBy('courseName')
            ->get();

        $query = DB::table('tbl_lc_course_master as map')
            ->join('tbl_lead_owner_lc_master as lc', 'lc.id', '=', 'map.fk_lc')
            ->join('tbl_course_master as c', 'c.id', '=', 'map.fk_course')
            ->leftJoin('tbl_tl_master as tl', 'tl.id', '=', 'lc.fk_tl')
            ->whereNull('tl.deleted_at')
            ->select(
                'map.id',
                'lc.id as lc_id', 'lc.lcName', 'lc.location as lc_location',
                'c.id as course_id', 'c.courseName', 'c.courseLocation as course_location',
                'map.updated_on'
            )
            ->orderBy('lc.location')
            ->orderBy('lc.lcName')
            ->orderBy('c.courseName');

        if (!empty($selectedLocation)) {
            $query->where('lc.location', $selectedLocation);
        }

        if (!empty($selectedCourse)) {
            $query->where('c.id', $selectedCourse);
        }

        $rows = $query->get();

        return view('course.assigned', [
            'rows' => $rows,
            'locations' => $locations,
            'courses' => $courses,
            'selectedLocation' => $selectedLocation,
            'selectedCourse' => $selectedCourse,
        ]);
    }
}
