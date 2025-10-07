<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LeadController extends Controller
{
    public function fetchAndStoreSearchResults(Request $request)
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Lead.GetListByProperty?accessKey={$accessKey}&secretKey={$secretKey}";
        // Example search params, you may want to get these from $request
        $params = [
            ["Parameter" => "EmailAddress", "Value" => ""],
            ["Parameter" => "Phone", "Value" => ""],
            ["Parameter" => "FirstName", "Value" => ""],
            ["Parameter" => "LastName", "Value" => ""],
            ["Parameter" => "PageIndex", "Value" => 1],
            ["Parameter" => "PageSize", "Value" => 10],
        ];
        $response = Http::post($url, $params);
        $data = $response->json();

        // Only store the result if the API returned valid data (not null)
        $searchResult = null;
        if (!empty($data) && is_array($data)) {
            $searchResult = \App\Models\LeadSearchResult::create([
                'ListId' => $data['ListId'] ?? null,
                'PageIndex' => $data['PageIndex'] ?? null,
                'PageSize' => $data['PageSize'] ?? null,
                'result' => json_encode($data),
            ]);
        }

        // Store each lead in lead_search_leads
        $storedLeads = [];
        if (!empty($data['LeadPropertyList']) && is_array($data['LeadPropertyList'])) {
            foreach ($data['LeadPropertyList'] as $lead) {
                // Only process leads with a non-empty ProspectID
                if (empty($lead['ProspectID'])) {
                    continue;
                }
                $existing = \App\Models\LeadSearchLead::where('ProspectID', $lead['ProspectID'])->first();
                if (!$existing) {
                    $storedLeads[] = \App\Models\LeadSearchLead::create([
                        'ProspectAutoId' => $lead['ProspectAutoId'] ?? null,
                        'EmailAddress' => $lead['EmailAddress'] ?? null,
                        'Score' => $lead['Score'] ?? null,
                        'ProspectID' => $lead['ProspectID'] ?? null,
                        'OwnerId' => $lead['OwnerId'] ?? null,
                        'CreatedOn' => $lead['CreatedOn'] ?? null,
                        'IsStarredLead' => $lead['IsStarredLead'] ?? null,
                        'IsTaggedLead' => $lead['IsTaggedLead'] ?? null,
                        'CanUpdate' => $lead['CanUpdate'] ?? null,
                        'Total' => $lead['Total'] ?? null,
                        'raw' => json_encode($lead),
                    ]);
                } else {
                    $storedLeads[] = $existing;
                }
            }
        }

        return view('search_result_fetch', [
            'stored' => $searchResult,
            'raw' => $data,
            'storedLeads' => $storedLeads,
        ]);
    }

    public function fetchAndStoreActivity()
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $leadId = '77c39f21-06aa-423f-97c1-42f7061cbe4a';
        $url = "https://api-in21.leadsquared.com/v2/ProspectActivity.svc/Retrieve?accessKey={$accessKey}&secretKey={$secretKey}&leadId={$leadId}";
        $response = \Illuminate\Support\Facades\Http::post($url, []);
        $data = $response->json();
        $stored = [];
        if (!empty($data['ProspectActivities'])) {
            foreach ($data['ProspectActivities'] as $activity) {
                $known = [
                    'Id',
                    'EventCode',
                    'EventName',
                    'ActivityScore',
                    'CreatedOn',
                    'ActivityType',
                    'Type',
                    'IsEmailType',
                    'SessionId',
                    'TrackLocation',
                    'Latitude',
                    'Longitude',
                    'RelatedProspectId',
                    'ModifiedOn'
                ];
                $toInsert = [];
                foreach ($known as $col) {
                    $val = $activity[$col] ?? null;
                    if (in_array($col, ['CreatedOn', 'ModifiedOn']) && ($val === '0001-01-01 00:00:00' || empty($val))) {
                        $val = null;
                    }
                    $toInsert[$col] = $val;
                }
                $toInsert['lead_id'] = $leadId;
                $toInsert['Data'] = isset($activity['Data']) ? json_encode($activity['Data']) : null;
                $toInsert['ActivityFields'] = isset($activity['ActivityFields']) ? json_encode($activity['ActivityFields']) : null;
                $stored[] = \App\Models\LeadActivity::updateOrCreate([
                    'Id' => $activity['Id']
                ], $toInsert);
            }
        }
        return view('activity_fetch_result', [
            'stored' => $stored,
            'raw' => $data['ProspectActivities'] ?? []
        ]);
    }

    public function fetchAndStoreLead($leadId = null)
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');

        // If no lead ID is provided, use the default one
        if (!$leadId) {
            $leadId = '77c39f21-06aa-423f-97c1-42f7061cbe4a';
        }

        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads.GetById?accessKey={$accessKey}&secretKey={$secretKey}&id={$leadId}";
        $response = \Illuminate\Support\Facades\Http::get($url);
        $data = $response->json();
        if (!empty($data[0])) {
            $leadData = $data[0];
            // Separate known columns and extra
            $known = [
                'ProspectID',
                'ProspectAutoId',
                'StatusCode',
                'StatusReason',
                'IsLead',
                'NotableEvent',
                'NotableEventdate',
                'LastVisitDate',
                'FirstName',
                'LastName',
                'EmailAddress',
                'Phone',
                'Company',
                'Source',
                'SourceCampaign',
                'JobTitle',
                'Score',
                'EngagementScore',
                'ProspectStage',
                'OwnerId',
                'OwnerIdName',
                'OwnerIdEmailAddress',
                'CreatedBy',
                'CreatedByName',
                'CreatedOn',
                'LeadConversionDate',
                'ModifiedBy',
                'ModifiedByName',
                'ModifiedOn',
                'mx_City',
                'mx_Course_Interested',
                'mx_Education_Completed',
                'mx_Ad_Name',
                'mx_campaign_Id',
                'mx_Adset_Name',
                'mx_Facebook_Form',
                'mx_Facebook_Page',
                'mx_Status',
                'mx_Outcome',
                'mx_Lead_Course',
                'mx_AD_Network',
                'mx_utm_creative_id',
                'mx_FB_LeadGen_ID',
                'mx_Program_Type',
                'mx_Source_Category',
                'mx_Courses_Category',
                'mx_Total_Calls_in_Lead',
                'mx_Level_of_interest',
                'mx_Primary_reason_for_course'
            ];
            $toInsert = [];
            foreach ($known as $col) {
                $toInsert[$col] = $leadData[$col] ?? null;
            }
            // Store all other fields in extra
            $extra = collect($leadData)->except($known)->toArray();
            $toInsert['extra'] = json_encode($extra);
            \App\Models\Lead::updateOrCreate([
                'ProspectID' => $leadData['ProspectID']
            ], $toInsert);
        }
        return redirect()->route('lead.index')->with('success', 'Lead data fetched and stored.');
    }

    public function getLeadById(Request $request)
    {
        $query = \App\Models\Lead::query();
        $hasFilter = false;
        if ($request->filled('first_name')) {
            $query->where('FirstName', 'like', '%' . $request->input('first_name') . '%');
            $hasFilter = true;
        }
        if ($request->filled('email')) {
            $query->where('EmailAddress', 'like', '%' . $request->input('email') . '%');
            $hasFilter = true;
        }
        if ($request->filled('phone')) {
            $query->where('Phone', 'like', '%' . $request->input('phone') . '%');
            $hasFilter = true;
        }
        if ($request->filled('status')) {
            $query->where('ProspectStage', 'like', '%' . $request->input('status') . '%');
            $hasFilter = true;
        }
        // Always get last 100 records for user experience
        $last100 = \App\Models\Lead::orderByDesc('CreatedOn')->limit(100)->get();
        // If filter, show filtered paginated, else show last 100 paginated
        if ($hasFilter) {
            $leads = $query->paginate(10);
        } else {
            $leads = $last100->take(10); // show first 10 for pagination effect
        }
        return view('lead', [
            'leads' => $leads,
            'filters' => $request->all(),
            'last100' => $last100
        ]);
    }

    public function getLeadActivity()
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $leadId = '77c39f21-06aa-423f-97c1-42f7061cbe4a';
        $url = "https://api-in21.leadsquared.com/v2/ProspectActivity.svc/Retrieve?accessKey={$accessKey}&secretKey={$secretKey}&leadId={$leadId}";
        $response = Http::post($url, []);
        $data = $response->json();
        return view('lead_activity', [
            'recordCount' => $data['RecordCount'] ?? 0,
            'activities' => $data['ProspectActivities'] ?? []
        ]);
    }

    /**
     * Fetch and store leads using the BySearchParameter API endpoint.
     */
    public function fetchAndStoreLeadsBySearchParameter(Request $request)
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads/Retrieve/BySearchParameter?accessKey={$accessKey}&secretKey={$secretKey}";

        $storedLeads = [];
        $searchResult = null;
        $data = [];

        // Example payload, you may want to get these from $request
        // If GET, show paginated leads from DB
        if ($request->isMethod('get')) {
            $perPage = 20; // Number of items per page
            $storedLeads = \App\Models\LeadSearchLead::orderByDesc('CreatedOn')->paginate($perPage);
            return view('lead_by_search_parameter', [
                'storedLeads' => $storedLeads
            ]);
        }

        // If POST, keep API fetch/store logic
        if ($request->filled('payload')) {
            $payload = json_decode($request->input('payload'), true);
            $response = Http::post($url, $payload);
            $data = $response->json();

            if (!empty($data['Leads']) && is_array($data['Leads'])) {
                foreach ($data['Leads'] as $leadBlock) {
                    if (empty($leadBlock['LeadPropertyList']) || !is_array($leadBlock['LeadPropertyList'])) {
                        continue;
                    }
                    // Convert LeadPropertyList to associative array
                    $leadArr = [];
                    foreach ($leadBlock['LeadPropertyList'] as $prop) {
                        if (!empty($prop['Attribute'])) {
                            $leadArr[$prop['Attribute']] = $prop['Value'] ?? null;
                        }
                    }
                    if (empty($leadArr['ProspectID'])) {
                        continue;
                    }
                    $existing = \App\Models\LeadSearchLead::where('ProspectID', $leadArr['ProspectID'])->first();
                    if (!$existing) {
                        // Prepare data for all columns
                        $dataToInsert = $leadArr;
                        // Convert all date/datetime fields to Y-m-d H:i:s or null
                        $dateFields = [
                            'CreatedOn',
                            'LeadConversionDate',
                            'ModifiedOn',
                            'NotableEventdate',
                            'LastVisitDate',
                            'FirstLandingPageSubmissionDate',
                            'ProspectActivityDate_Max',
                            'ProspectActivityDate_Min',
                            'LeadLastModifiedOn',
                            'OptInDate',
                            'LastOptInEmailSentDate',
                            'mx_Date_of_Birth',
                            'mx_Joined_Date',
                            'mx_Follow_Up_Date_Time',
                            'mx_AD_Click_Date',
                            'mx_Last_Follow_Up_Date',
                            'mx_FB_Recapture_Date',
                            'mx_Conversion_Date',
                            'mx_First_lead_Date',
                            'mx_Re_Submission_Date',
                            'mx_Interested_on_date',
                            'mx_Enrolled_At'
                        ];
                        foreach ($dateFields as $field) {
                            if (!empty($dataToInsert[$field])) {
                                $dt = date_create($dataToInsert[$field]);
                                $dataToInsert[$field] = $dt ? $dt->format('Y-m-d H:i:s') : null;
                            }
                        }
                        // Robust boolean normalization for IsStarredLead, IsTaggedLead, CanUpdate
                        foreach (['IsStarredLead', 'IsTaggedLead', 'CanUpdate'] as $boolField) {
                            if (isset($dataToInsert[$boolField])) {
                                $val = $dataToInsert[$boolField];
                                if ($val === 'true' || $val === true || $val === 1 || $val === '1') {
                                    $dataToInsert[$boolField] = 1;
                                } elseif ($val === 'false' || $val === false || $val === 0 || $val === '0') {
                                    $dataToInsert[$boolField] = 0;
                                } else {
                                    $dataToInsert[$boolField] = null;
                                }
                            }
                        }
                        // JSON fields
                        foreach (['mx_Document', 'mx_Documents', 'mx_Registration_Document'] as $jsonField) {
                            if (isset($dataToInsert[$jsonField]) && is_string($dataToInsert[$jsonField])) {
                                $jsonVal = json_decode($dataToInsert[$jsonField], true);
                                $dataToInsert[$jsonField] = $jsonVal !== null ? json_encode($jsonVal) : null;
                            }
                        }
                        $dataToInsert['raw'] = json_encode($leadArr);
                        \App\Models\LeadSearchLead::create($dataToInsert);
                    }
                }
            }
            // After storing, redirect to GET for pagination
            return redirect()->route('lead.fetch-by-search-parameter');
        }
    }

    /**
     * Export a single lead and all its activities to an Excel-compatible CSV, without using DB.
     *
     * Request: GET /lead/export/full?leadId={GUID}
     * - Uses the provided leadId (ProspectID) to:
     *   1) Call ProspectActivity.svc/Retrieve/BySearchParameter (for reference/validation)
     *   2) Call LeadManagement.svc/Leads.GetById to get lead details
     *   3) Call ProspectActivity.svc/Retrieve to get all activities for the lead
     * The output is streamed as CSV to keep memory usage low and performance high.
     */
    // public function exportLeadFullDetails(Request $request, $leadId = "66b97513-920e-11f0-9791-06b8222c9ed1")
    // {
    //     // Avoid 60 seconds fatal during large exports
    //     @set_time_limit(0);
    //     // Optional: raise memory if needed
    //     // @ini_set('memory_limit', '512M');
    //     $leadId = $leadId ?: $request->query('leadId');
    //     // $test = $this->testBySearchParam($request);
    //     // dd($test);


    //     if (empty($leadId)) {
    //         return response()->json(['message' => 'Missing required query parameter: leadId'], 400);
    //     }

    //     $accessKey = env('LEADSQUARED_ACCESS_KEY');
    //     $secretKey = env('LEADSQUARED_SECRET_KEY');
    //     // dd($accessKey, $secretKey);


    //     $bySearchJson = $this->testBySearchParam($request);

    //     // Make sure we have results (normalize if it's a JsonResponse)
    //     if ($bySearchJson instanceof \Illuminate\Http\JsonResponse) {
    //         $bySearchJson = $bySearchJson->getData(true);
    //     }

    //     // Expecting structure from Leads/Retrieve/BySearchParameter:
    //     // { RecordCount: N, Leads: [ { LeadPropertyList: [ { Attribute: 'ProspectID', Value: '...' }, ... ] }, ... ] }
    //     $firstLead = $bySearchJson['Leads'][0] ?? null;
    //     $leadProps = $firstLead['LeadPropertyList'] ?? null;
    //     // Removed dd($bySearchJson['Leads']); to allow processing of all leads
    //     $prospectId = null;
    //     if (is_array($leadProps)) {
    //         foreach ($leadProps as $prop) {
    //             if (($prop['Attribute'] ?? null) === 'ProspectID') {
    //                 $prospectId = $prop['Value'] ?? null;
    //                 break;
    //             }
    //         }
    //     }

    //     // Prepare CSV header fields
    //     $leadColumns = [
    //         'ProspectID',
    //         'FirstName',
    //         'LastName',
    //         'EmailAddress',
    //         'Phone',
    //         'SourceCampaign',
    //         'SourceMediu',
    //         'SourceConten',
    //         'ProspectStage',
    //         'OwnerId',
    //         'CreatedByName',
    //         'CreatedOn',
    //         'mx_Lead_URL',
    //         'mx_City',
    //         'mx_Country',
    //         'OwnerIdName',
    //         'mx_Course_Interested',
    //         'mx_Ad_Name',
    //         'mx_campaign_Id',
    //         'mx_Adset_Name',
    //         'mx_UTM_Sourc',
    //         'mx_UTM_Ter',
    //         'mx_Facebook_Form',
    //         'mx_Facebook_Page',
    //         'mx_Status',
    //         'mx_GCLID',
    //         'Notes',
    //         'mx_Total_Calls_in_Lead',
    //         'mx_Total_Answered_Calls'
    //     ];

    //     $extraMetaColumns = ['BySearch_RecordCount'];

    //     // Build lead list and record count from BySearch response
    //     $leadsArr = $bySearchJson['Leads'] ?? [];
    //     $bySearchCount = $bySearchJson['RecordCount'] ?? ($bySearchJson['Total'] ?? '');

    //     $filename = 'lead_export_batch_' . date('Ymd_His') . '.csv';

    //     return response()->streamDownload(function () use ($leadsArr, $leadColumns, $extraMetaColumns, $bySearchCount, $accessKey, $secretKey) {
    //         $out = fopen('php://output', 'w');

    //         // Build header row
    //         $headers = array_merge($leadColumns, $extraMetaColumns);
    //         fputcsv($out, $headers);

    //         foreach ($leadsArr as $leadBlock) {
    //             // Convert LeadPropertyList to associative array
    //             $leadMap = [];
    //             if (!empty($leadBlock['LeadPropertyList']) && is_array($leadBlock['LeadPropertyList'])) {
    //                 foreach ($leadBlock['LeadPropertyList'] as $prop) {
    //                     if (!empty($prop['Attribute'])) {
    //                         $leadMap[$prop['Attribute']] = $prop['Value'] ?? null;
    //                     }
    //                 }
    //             }

    //             $prospectId = $leadMap['ProspectID'] ?? null;
    //             if (empty($prospectId)) {
    //                 continue;
    //             }

    //             // 2) Get the lead details by ProspectID, with timeout/retry and exception safety
    //             $leadDetails = [];
    //             try {
    //                 $urlLeadById = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads.GetById?accessKey={$accessKey}&secretKey={$secretKey}&id={$prospectId}";
    //                 $leadResp = Http::timeout(12)->retry(2, 200)->get($urlLeadById);
    //                 if ($leadResp->successful()) {
    //                     $leadJsonArr = $leadResp->json();
    //                     $leadDetails = is_array($leadJsonArr) && !empty($leadJsonArr[0]) ? $leadJsonArr[0] : [];
    //                 }
    //             } catch (\Throwable $e) {
    //                 // Keep row minimal if API errors out; no HTML error should leak into CSV
    //                 $leadDetails = [];
    //             }

    //             // Build the lead row values in column order
    //             $leadRow = [];
    //             $forceStringCols = ['ProspectID', 'ProspectAutoId', 'Phone'];
    //             foreach ($leadColumns as $col) {
    //                 $val = $leadDetails[$col] ?? '';
    //                 // Prevent Excel scientific notation for IDs/phones by forcing text
    //                 if (in_array($col, $forceStringCols, true) && $val !== '') {
    //                     // Prefix with tab to hint Excel as text without showing apostrophe in cell
    //                     $val = "\t" . (string) $val;
    //                 }
    //                 $leadRow[] = $val;
    //             }

    //             // Do not call activities API; write a single row per lead
    //             $row = array_merge($leadRow, [$bySearchCount]);
    //             fputcsv($out, $row);
    //         }

    //         fclose($out);
    //     }, $filename, [
    //         'Content-Type' => 'text/csv',
    //     ]);
    // }
    public function exportLeadFullDetails(Request $request)
    {
        $bySearchJson = $this->testBySearchParam($request);
        // Normalize JsonResponse to array
        if ($bySearchJson instanceof \Illuminate\Http\JsonResponse) {
            $bySearchJson = $bySearchJson->getData(true);
        }
        // dd($bySearchJson); // Got All Array
        // Leads are inside "Leads" array
        
        $firstLead = $bySearchJson['Leads'][0] ?? null; // Got First Row Prospect ID
        // dd($firstLead);
        // dd($firstLead['LeadPropertyList'][0]['Value']);
        
        $prospectId = null;
        if ($firstLead && !empty($firstLead['LeadPropertyList'])) {
            foreach ($firstLead['LeadPropertyList'] as $prop) {
                if (($prop['Attribute'] ?? '') === 'ProspectID') {
                    $prospectId = $prop['Value'] ?? null;
                    break;
                }
            }
        }
        
        if (empty($prospectId)) {
            dd('No ProspectID found in API response', $bySearchJson);
        }
        
        $leadDetails = $this->testDetailsByGetId($prospectId); // Based on prospect Id got lead details
        dd($leadDetails);
    }
    


    public function testBySearchParam(Request $request)
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');

        // Example body
        $listId = $request->query('listId', '66b97513-920e-11f0-9791-06b8222c9ed1');
        $pageSize = (int) $request->query('pageSize', 10000);
        $pageIndex = (int) $request->query('pageIndex', 1);

        $searchArrayParam = [
            "SearchParameters" => [
                "ListId" => $listId,
                "RetrieveBehaviour" => "0",
            ],
            "Columns" => [
                // mirror user's curl; you can adjust fields as needed
                "Include_CSV" => "ProspectID,ProspectAutoId,EmailAddress,Score,",
            ],
            "Sorting" => [
                "ColumnName" => "CreatedOn",
                "Direction" => "1",
            ],
            "Paging" => [
                "PageIndex" => $pageIndex,
                "PageSize" => $pageSize,
            ],
        ];
        // Use the Leads endpoint (matches your curl)
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads/Retrieve/BySearchParameter?accessKey={$accessKey}&secretKey={$secretKey}";

        // Prefer Laravel HTTP client for consistency and easier debugging
        $resp = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($url, $searchArrayParam);

        // Try to parse JSON; if it fails, return status and raw body for inspection
        $json = null;
        try {
            $json = $resp->json();
        } catch (\Throwable $e) {
            // ignore, will fall back to raw body
        }

        if ($json === null) {
            return response()->json([
                'message' => 'Non-JSON or empty response from API',
                'status' => $resp->status(),
                'body' => $resp->body(),
            ], $resp->status() ?: 502);
        }

        return response()->json($json, $resp->status());
    }

    public function testDetailsByGetId($leadId = null)
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');

        // If no lead ID is provided, use the default one
        if (!$leadId) {
            $leadId = '77c39f21-06aa-423f-97c1-42f7061cbe4a';
        }

        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads.GetById?accessKey={$accessKey}&secretKey={$secretKey}&id={$leadId}";
        $response = \Illuminate\Support\Facades\Http::get($url);
        $data = $response->json();
        if (!empty($data[0])) {
            $leadData = $data[0];
            // Separate known columns and extra
            $known = [
                'ProspectID',
                'ProspectAutoId',
                'StatusCode',
                'StatusReason',
                'IsLead',
                'NotableEvent',
                'NotableEventdate',
                'LastVisitDate',
                'FirstName',
                'LastName',
                'EmailAddress',
                'Phone',
                'Company',
                'Source',
                'SourceCampaign',
                'JobTitle',
                'Score',
                'EngagementScore',
                'ProspectStage',
                'OwnerId',
                'OwnerIdName',
                'OwnerIdEmailAddress',
                'CreatedBy',
                'CreatedByName',
                'CreatedOn',
                'LeadConversionDate',
                'ModifiedBy',
                'ModifiedByName',
                'ModifiedOn',
                'mx_City',
                'mx_Course_Interested',
                'mx_Education_Completed',
                'mx_Ad_Name',
                'mx_campaign_Id',
                'mx_Adset_Name',
                'mx_Facebook_Form',
                'mx_Facebook_Page',
                'mx_Status',
                'mx_Outcome',
                'mx_Lead_Course',
                'mx_AD_Network',
                'mx_utm_creative_id',
                'mx_FB_LeadGen_ID',
                'mx_Program_Type',
                'mx_Source_Category',
                'mx_Courses_Category',
                'mx_Total_Calls_in_Lead',
                'mx_Level_of_interest',
                'mx_Primary_reason_for_course'
            ];
            $toInsert = [];
            foreach ($known as $col) {
                $toInsert[$col] = $leadData[$col] ?? null;
            }
            // Store all other fields in extra
            $extra = collect($leadData)->except($known)->toArray();
            $toInsert['extra'] = json_encode($extra);
            // dd($toInsert);
            return $toInsert;
        }
        return null;
    }
}
