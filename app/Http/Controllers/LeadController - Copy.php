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
                    'Id','EventCode','EventName','ActivityScore','CreatedOn','ActivityType','Type','IsEmailType','SessionId','TrackLocation','Latitude','Longitude','RelatedProspectId','ModifiedOn'
                ];
                $toInsert = [];
                foreach ($known as $col) {
                    $val = $activity[$col] ?? null;
                    if (in_array($col, ['CreatedOn','ModifiedOn']) && ($val === '0001-01-01 00:00:00' || empty($val))) {
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
    public function fetchAndStoreLead()
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $leadId = '77c39f21-06aa-423f-97c1-42f7061cbe4a';
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads.GetById?accessKey={$accessKey}&secretKey={$secretKey}&id={$leadId}";
        $response = \Illuminate\Support\Facades\Http::get($url);
        $data = $response->json();
        if (!empty($data[0])) {
            $leadData = $data[0];
            // Separate known columns and extra
            $known = [
                'ProspectID','ProspectAutoId','StatusCode','StatusReason','IsLead','NotableEvent','NotableEventdate','LastVisitDate','FirstName','LastName','EmailAddress','Phone','Company','Source','SourceCampaign','JobTitle','Score','EngagementScore','ProspectStage','OwnerId','OwnerIdName','OwnerIdEmailAddress','CreatedBy','CreatedByName','CreatedOn','LeadConversionDate','ModifiedBy','ModifiedByName','ModifiedOn','mx_City','mx_Course_Interested','mx_Education_Completed','mx_Ad_Name','mx_campaign_Id','mx_Adset_Name','mx_Facebook_Form','mx_Facebook_Page','mx_Status','mx_Outcome','mx_Lead_Course','mx_AD_Network','mx_utm_creative_id','mx_FB_LeadGen_ID','mx_Program_Type','mx_Source_Category','mx_Courses_Category','mx_Total_Calls_in_Lead','mx_Level_of_interest','mx_Primary_reason_for_course'
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

        // Example payload, you may want to get these from $request
        // If payload is provided (from form), decode it, else use default
        if ($request->filled('payload')) {
            $payload = json_decode($request->input('payload'), true);
        } else {
            $payload = [
                "SearchParameters" => [
                    "ListId" => $request->input('ListId', '50ade829-8ee1-11f0-9791-06b8222c9ed1'),
                    "RetrieveBehaviour" => $request->input('RetrieveBehaviour', 0),
                ],
                "Columns" => [
                    "Include_CSV" => $request->input('Include_CSV', 'ProspectAutoId,EmailAddress,Score,'),
                ],
                "Sorting" => [
                    "ColumnName" => $request->input('ColumnName', 'CreatedOn'),
                    "Direction" => $request->input('Direction', 1),
                ],
                "Paging" => [
                    "PageIndex" => $request->input('PageIndex', 1),
                    "PageSize" => $request->input('PageSize', 1000),
                ],
            ];
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, $payload);
        $data = $response->json();

        // Only store the result if the API returned valid data (not null)
        $searchResult = null;
        if (!empty($data) && is_array($data)) {
            $searchResult = \App\Models\LeadSearchResult::create([
                'ListId' => $payload['SearchParameters']['ListId'] ?? null,
                'PageIndex' => $payload['Paging']['PageIndex'] ?? null,
                'PageSize' => $payload['Paging']['PageSize'] ?? null,
                'result' => json_encode($data),
            ]);
        }

        // Store each lead in lead_search_leads (skip if ProspectID is empty or already exists)
        $storedLeads = [];
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
                        'CreatedOn','LeadConversionDate','ModifiedOn','NotableEventdate','LastVisitDate','FirstLandingPageSubmissionDate','ProspectActivityDate_Max','ProspectActivityDate_Min','LeadLastModifiedOn','OptInDate','LastOptInEmailSentDate','mx_Date_of_Birth','mx_Joined_Date','mx_Follow_Up_Date_Time','mx_AD_Click_Date','mx_Last_Follow_Up_Date','mx_FB_Recapture_Date','mx_Conversion_Date','mx_First_lead_Date','mx_Re_Submission_Date','mx_Interested_on_date','mx_Enrolled_At'
                    ];
                    foreach ($dateFields as $field) {
                        if (!empty($dataToInsert[$field])) {
                            $dt = date_create($dataToInsert[$field]);
                            $dataToInsert[$field] = $dt ? $dt->format('Y-m-d H:i:s') : null;
                        }
                    }
                    // Convert boolean-like fields to integer
                    foreach (['IsStarredLead','IsTaggedLead','CanUpdate'] as $boolField) {
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
                    foreach (['mx_Document','mx_Documents','mx_Registration_Document'] as $jsonField) {
                        if (isset($dataToInsert[$jsonField]) && is_string($dataToInsert[$jsonField])) {
                            $jsonVal = json_decode($dataToInsert[$jsonField], true);
                            $dataToInsert[$jsonField] = $jsonVal !== null ? json_encode($jsonVal) : null;
                        }
                    }
                    $dataToInsert['raw'] = json_encode($leadArr);
                    $storedLeads[] = \App\Models\LeadSearchLead::create($dataToInsert);
                } else {
                    $storedLeads[] = $existing;
                }
            }
        }

        return view('lead_by_search_parameter', [
            'stored' => $searchResult,
            'raw' => $data,
            'storedLeads' => $storedLeads,
        ]);
    }
}
