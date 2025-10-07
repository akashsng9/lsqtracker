<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LeadSourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'all');
        $query = LeadSource::query();

        // Apply tab filter
        if ($activeTab === 'organic') {
            $query->where('sourceType', 'Organic');
        } elseif ($activeTab === 'paid') {
            $query->where('sourceType', 'Paid');
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('leadSource', 'like', '%' . $search . '%')
                    ->orWhere('sourceType', 'like', '%' . $search . '%');
            });
        }

        $leadSources = $query->orderBy('leadSource')->get();

        // Counts for tabs
        $allCount = LeadSource::count();
        $organicCount = LeadSource::where('sourceType', 'Organic')->count();
        $paidCount = LeadSource::where('sourceType', 'Paid')->count();

        // Get distinct lead sources from leads table for the dropdown
        $leadDropdownOptions = Lead::select(trim('Source'))
            ->distinct()
            ->where('Source', '!=', '')
            ->orderBy('Source')
            ->get();

        $sourceCampaign = DB::select("
            SELECT id, Source, SourceCampaign
            FROM (
                SELECT 
                    id,
                    CASE 
                        WHEN Source IS NOT NULL AND Source <> '' THEN Source
                        WHEN SourceCampaign LIKE '%AOLT%' AND SourceCampaign LIKE '%FB%' THEN 'Facebook'
                        WHEN SourceCampaign LIKE '%AOST%' AND SourceCampaign LIKE '%FB%' THEN 'Facebook'
                        WHEN SourceCampaign LIKE '%AOLT%' AND SourceCampaign LIKE '%search%' THEN 'Google'
                        WHEN SourceCampaign LIKE '%AOST%' AND SourceCampaign LIKE '%search%' THEN 'Google'
                        ELSE 'Unknown'
                    END AS Source,
                    SourceCampaign,
                    ROW_NUMBER() OVER (
                        PARTITION BY SourceCampaign 
                        ORDER BY CASE WHEN Source IS NOT NULL AND Source <> '' THEN 0 ELSE 1 END, id DESC
                    ) as rn
                FROM leads WHERE created_at >= NOW() - INTERVAL 3 MONTH
            ) t
            WHERE rn = 1
            ORDER BY id DESC
        ");

        // dd($sourceCampaign);

        return view('lead-source.index', [
            'sources' => $leadDropdownOptions,
            'leadSources' => $leadSources,
            'activeTab' => $activeTab,
            'sourceCampaign' => $sourceCampaign,
            'counts' => [
                'all' => $allCount,
                'organic' => $organicCount,
                'paid' => $paidCount
            ],
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $source = LeadSource::findOrFail($id);
            return view('lead-source.edit', compact('source'));
        } catch (\Exception $e) {
            Log::error('Error fetching lead source for edit: ' . $e->getMessage());
            return redirect()->route('lead-sources.index')
                ->with('error', 'Lead source not found.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        Log::info('Incoming Lead Source Request:', $request->all());

        try {
            // Determine which option was selected: leadSource or sourceCampaign
            $sourceOption = $request->sourceOption; // must match radio button name
            $sourceType   = $request->sourceType;   // Paid or Organic

            // Get value based on selected option
            $value = $sourceOption === 'leadSource'
                ? $request->leadSource
                : $request->sourceCampaign;

            // Ensure value is not empty
            if (empty($value)) {
                $errorMessage = 'Please select or type a value for the chosen option.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $errorMessage], 422)
                    : back()->with('error', $errorMessage);
            }

            // Determine type
            $type = $sourceOption === 'leadSource' ? 1 : 2;

            // Check duplicate
            $exists = LeadSource::where('leadSource', $value)
                ->where('type', $type)
                ->exists();

            if ($exists) {
                $errorMessage = 'This entry already exists for the selected source type.';
                return $request->ajax()
                    ? response()->json(['success' => false, 'message' => $errorMessage], 409)
                    : back()->with('error', $errorMessage);
            }

            // Create record
            $leadSource = LeadSource::create([
                'leadSource'  => $value,
                'sourceType'  => $sourceType,
                'createdBy'   => auth()->id(),
                'type'        => $type,
                'createdDate' => now(),
                'isActive'    => 1,
            ]);

            // Success response
            $successMessage = 'Lead source created successfully!';
            return $request->ajax()
                ? response()->json(['success' => true, 'message' => $successMessage, 'data' => $leadSource])
                : redirect()->route('lead-sources.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            Log::error('Error creating lead source:', ['message' => $e->getMessage()]);
            $errorMessage = 'An error occurred while creating the lead source. Please try again.';
            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $errorMessage], 500)
                : back()->with('error', $errorMessage);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::info('Update method called', ['id' => $id, 'request' => $request->all()]);

        try {
            $source = LeadSource::findOrFail($id);
            Log::info('Found source', ['source' => $source]);

            $validated = $request->validate([
                'leadSource' => 'required|string|max:255|unique:tbl_lead_source_master,leadSource,' . $id,
                'sourceType' => 'required|in:Paid,Organic',
            ]);
            Log::info('Validation passed', ['validated' => $validated]);

            $source->update($validated);
            Log::info('Source updated successfully');

            return redirect()->route('lead-sources.index')
                ->with('success', 'Lead source updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error', ['errors' => $e->errors()]);
            return back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating lead source: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()->with('error', 'Failed to update lead source. ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $source = LeadSource::findOrFail($id);
            $source->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead source deleted successfully.'
                ]);
            }

            return redirect()->route('lead-sources.index')
                ->with('success', 'Lead source deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting lead source: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete lead source.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete lead source.');
        }
    }
}
