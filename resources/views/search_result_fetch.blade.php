@extends('layouts.app')
@section('title', 'Lead Search API Fetch Result')
@section('content')
    <div class="card">
        <div class="card-body">
            <h2>Lead Search API Fetch Result</h2>
            <div class="alert alert-success">Lead search API data fetched and stored in the database.</div>
            <h4>Stored Result ID: {{ $stored ? $stored->id : 'N/A' }}</h4>
            <h4>Stored Leads:</h4>
            @if(!empty($storedLeads))
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ProspectID</th>
                            <th>IsLead</th>
                            <th>NotableEvent</th>
                            <th>NotableEventdate</th>
                            <th>ProspectActivityName_Max</th>
                            <th>ProspectActivityDate_Max</th>
                            <th>LeadLastModifiedOn</th>
                            <th>FirstName</th>
                            <th>LastName</th>
                            <th>EmailAddress</th>
                            <th>Phone</th>
                            <th>SourceCampaign</th>
                            <th>SourceMedium</th>
                            <th>SourceContent</th>
                            <th>Score</th>
                            <th>ProspectStage</th>
                            <th>OwnerId</th>
                            <th>CreatedByName</th>
                            <th>CreatedOn</th>
                            <th>LeadConversionDate</th>
                            <th>ModifiedBy</th>
                            <th>ModifiedByName</th>
                            <th>mx_Lead_URL</th>
                            <th>mx_City</th>
                            <th>mx_Country</th>
                            <th>OwnerIdName</th>
                            <th>OwnerIdEmailAddress</th>
                            <th>Origin</th>
                            <th>mx_Course_Interested</th>
                            <th>mx_Ad_Name</th>
                            <th>mx_campaign_Id</th>
                            <th>mx_Adset_Name</th>
                            <th>mx_UTM_Source</th>
                            <th>mx_UTM_Term</th>
                            <th>mx_Facebook_Form</th>
                            <th>mx_Facebook_Page</th>
                            <th>mx_Status</th>
                            <th>mx_Outcome</th>
                            <th>mx_Lead_Course</th>
                            <th>mx_GCLID</th>
                            <th>mx_Primary_reason_for_course</th>
                            <th>mx_utm_creative_id</th>
                            <th>mx_FB_LeadGen_ID</th>
                            <th>mx_Program_Type</th>
                            <th>mx_Source_Category</th>
                            <th>mx_Courses_Category</th>
                            <th>Notes</th>
                            <th>mx_Total_Calls_in_Lead</th>
                            <th>mx_Total_Answered_Calls</th>
                            <th>mx_Last_Follow_Up_Date</th>
                            <th>mx_Pre_Qualified_Leads</th>
                            <th>mx_Activity_Notes</th>
                            <th>mx_utm_medium</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($storedLeads as $lead)
                            <tr>
                                <td>{{ $lead->ProspectID }}</td>
                                <td>{{ $lead->IsLead }}</td>
                                <td>{{ $lead->NotableEvent }}</td>
                                <td>{{ $lead->NotableEventdate }}</td>
                                <td>{{ $lead->ProspectActivityName_Max }}</td>
                                <td>{{ $lead->ProspectActivityDate_Max }}</td>
                                <td>{{ $lead->LeadLastModifiedOn }}</td>
                                <td>{{ $lead->FirstName }}</td>
                                <td>{{ $lead->LastName }}</td>
                                <td>{{ $lead->EmailAddress }}</td>
                                <td>{{ $lead->Phone }}</td>
                                <td>{{ $lead->SourceCampaign }}</td>
                                <td>{{ $lead->SourceMedium }}</td>
                                <td>{{ $lead->SourceContent }}</td>
                                <td>{{ $lead->Score }}</td>
                                <td>{{ $lead->ProspectStage }}</td>
                                <td>{{ $lead->OwnerId }}</td>
                                <td>{{ $lead->CreatedByName }}</td>
                                <td>{{ $lead->CreatedOn }}</td>
                                <td>{{ $lead->LeadConversionDate }}</td>
                                <td>{{ $lead->ModifiedBy }}</td>
                                <td>{{ $lead->ModifiedByName }}</td>
                                <td>{{ $lead->mx_Lead_URL }}</td>
                                <td>{{ $lead->mx_City }}</td>
                                <td>{{ $lead->mx_Country }}</td>
                                <td>{{ $lead->OwnerIdName }}</td>
                                <td>{{ $lead->OwnerIdEmailAddress }}</td>
                                <td>{{ $lead->Origin }}</td>
                                <td>{{ $lead->mx_Course_Interested }}</td>
                                <td>{{ $lead->mx_Ad_Name }}</td>
                                <td>{{ $lead->mx_campaign_Id }}</td>
                                <td>{{ $lead->mx_Adset_Name }}</td>
                                <td>{{ $lead->mx_UTM_Source }}</td>
                                <td>{{ $lead->mx_UTM_Term }}</td>
                                <td>{{ $lead->mx_Facebook_Form }}</td>
                                <td>{{ $lead->mx_Facebook_Page }}</td>
                                <td>{{ $lead->mx_Status }}</td>
                                <td>{{ $lead->mx_Outcome }}</td>
                                <td>{{ $lead->mx_Lead_Course }}</td>
                                <td>{{ $lead->mx_GCLID }}</td>
                                <td>{{ $lead->mx_Primary_reason_for_course }}</td>
                                <td>{{ $lead->mx_utm_creative_id }}</td>
                                <td>{{ $lead->mx_FB_LeadGen_ID }}</td>
                                <td>{{ $lead->mx_Program_Type }}</td>
                                <td>{{ $lead->mx_Source_Category }}</td>
                                <td>{{ $lead->mx_Courses_Category }}</td>
                                <td>{{ $lead->Notes }}</td>
                                <td>{{ $lead->mx_Total_Calls_in_Lead }}</td>
                                <td>{{ $lead->mx_Total_Answered_Calls }}</td>
                                <td>{{ $lead->mx_Last_Follow_Up_Date }}</td>
                                <td>{{ $lead->mx_Pre_Qualified_Leads }}</td>
                                <td>{{ $lead->mx_Activity_Notes }}</td>
                                <td>{{ $lead->mx_utm_medium }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning">No leads stored from this search.</div>
            @endif
            <h4>Raw API Data (truncated):</h4>
            @php
                $json = json_encode($raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $max = 50000; // limit to ~50KB to prevent memory blow-ups in view rendering
                $truncated = mb_substr($json ?? '', 0, $max, 'UTF-8');
                $isTruncated = ($json !== null) && (mb_strlen($json, 'UTF-8') > $max);
            @endphp
            @if(!empty($truncated))
                <pre>{{ $truncated }}@if($isTruncated)
...
[truncated]
@endif</pre>
            @else
                <div class="alert alert-info">No raw payload available.</div>
            @endif

            <a href="{{ url('/lead') }}" class="btn btn-primary mt-3">Back to Leads</a>
        </div>
    </div>
@endsection