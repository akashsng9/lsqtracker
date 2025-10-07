<?php

namespace App\Http\Controllers;

use App\Models\TLMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TLMasterController extends Controller
{
    public function index()
    {
        $tls = TLMaster::orderBy('location')->orderBy('tl_name')->get();
        return view('config.tl-index', compact('tls'));
    }

    public function create()
    {
        $lcLocations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $tlLocations = DB::table('tbl_tl_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $locations = collect(array_unique(array_filter(array_merge($lcLocations, $tlLocations))))
            ->sort()
            ->values();

        return view('config.tl-create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tl_name'  => 'required|string|max:255',
            'contact'  => 'nullable|string|max:50',
            'email'    => 'nullable|email|max:255',
            'location' => 'required|string|max:255',
            'status'   => 'required|in:0,1',
        ]);

        $validated['status'] = (int)$validated['status'];

        TLMaster::create($validated);

        return redirect()->route('configuration.tl.index')->with('success', 'TL created successfully');
    }

    public function edit(TLMaster $tl)
    {
        $lcLocations = DB::table('tbl_lead_owner_lc_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $tlLocations = DB::table('tbl_tl_master')
            ->select('location')
            ->whereNotNull('location')
            ->groupBy('location')
            ->pluck('location')
            ->toArray();

        $locations = collect(array_unique(array_filter(array_merge($lcLocations, $tlLocations))))
            ->sort()
            ->values();

        return view('config.tl-edit', compact('tl', 'locations'));
    }

    public function update(Request $request, TLMaster $tl)
    {
        $validated = $request->validate([
            'tl_name'  => 'required|string|max:255',
            'contact'  => 'nullable|string|max:50',
            'email'    => 'nullable|email|max:255',
            'location' => 'required|string|max:255',
            'status'   => 'required|in:0,1',
        ]);

        $validated['status'] = (int)$validated['status'];

        $tl->update($validated);

        return redirect()->route('configuration.tl.index')->with('success', 'TL updated successfully');
    }

    public function destroy(TLMaster $tl)
    {
        $tl->delete();
        return redirect()->route('configuration.tl.index')->with('success', 'TL deleted successfully');
    }
}
