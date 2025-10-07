<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Totals
        $totalCourses = DB::table('tbl_course_master')->count();
        $activeCourses = DB::table('tbl_course_master')->where('CourseStatus', 1)->count();

        $totalTLs = DB::table('tbl_tl_master')->whereNull('deleted_at')->count();
        $activeTLs = DB::table('tbl_tl_master')->whereNull('deleted_at')->where('status', 1)->count();

        $totalLCs = DB::table('tbl_lead_owner_lc_master')->count();
        $activeLCs = DB::table('tbl_lead_owner_lc_master')->where('status', 1)->count();

        // Breakdown by location (union of locations across entities)
        $courseLocs = DB::table('tbl_course_master')
            ->select('courseLocation as location')
            ->whereNotNull('courseLocation');

        $tlLocs = DB::table('tbl_tl_master')
            ->select('location')
            ->whereNull('deleted_at')
            ->whereNotNull('location');

        $lcLocs = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location');

        $locations = $courseLocs
            ->union($tlLocs)
            ->union($lcLocs)
            ->pluck('location');

        $locations = collect($locations)->filter()->unique()->sort()->values();

        $byLocation = [];
        foreach ($locations as $loc) {
            $byLocation[] = [
                'location' => $loc,
                'courses' => (int) DB::table('tbl_course_master')->where('courseLocation', $loc)->count(),
                'tls'     => (int) DB::table('tbl_tl_master')->whereNull('deleted_at')->where('location', $loc)->count(),
                'lcs'     => (int) DB::table('tbl_lead_owner_lc_master')->where('location', $loc)->count(),
            ];
        }

        return view('dashboard.index', [
            'totalCourses' => $totalCourses,
            'activeCourses' => $activeCourses,
            'totalTLs' => $totalTLs,
            'activeTLs' => $activeTLs,
            'totalLCs' => $totalLCs,
            'activeLCs' => $activeLCs,
            'byLocation' => $byLocation,
        ]);
    }
}
