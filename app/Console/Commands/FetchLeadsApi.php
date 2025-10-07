<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class FetchLeadsApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:leadsapi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accessKey = env('LEADSQUARED_ACCESS_KEY');
        $secretKey = env('LEADSQUARED_SECRET_KEY');
        $leadId = 'e7b02830-aae3-40d7-aa0f-200721d482be';
        $url = "https://api-in21.leadsquared.com/v2/LeadManagement.svc/Leads.GetById?accessKey={$accessKey}&secretKey={$secretKey}&id={$leadId}";
    $response = Http::get($url);
        $data = $response->json();
        if (!empty($data[0])) {
            $leadData = $data[0];
            $known = [
                'ProspectID','ProspectAutoId','StatusCode','StatusReason','IsLead','NotableEvent','NotableEventdate','LastVisitDate','FirstName','LastName','EmailAddress','Phone','Company','Source','SourceCampaign','JobTitle','Score','EngagementScore','ProspectStage','OwnerId','OwnerIdName','OwnerIdEmailAddress','CreatedBy','CreatedByName','CreatedOn','LeadConversionDate','ModifiedBy','ModifiedByName','ModifiedOn','mx_City','mx_Course_Interested','mx_Education_Completed','mx_Ad_Name','mx_campaign_Id','mx_Adset_Name','mx_Facebook_Form','mx_Facebook_Page','mx_Status','mx_Outcome','mx_Lead_Course','mx_AD_Network','mx_utm_creative_id','mx_FB_LeadGen_ID','mx_Program_Type','mx_Source_Category','mx_Courses_Category','mx_Total_Calls_in_Lead','mx_Level_of_interest','mx_Primary_reason_for_course'
            ];
            $toInsert = [];
            foreach ($known as $col) {
                $toInsert[$col] = $leadData[$col] ?? null;
            }
            $extra = collect($leadData)->except($known)->toArray();
            $toInsert['extra'] = json_encode($extra);
            Lead::updateOrCreate([
                'ProspectID' => $leadData['ProspectID']
            ], $toInsert);
            $this->info('Lead data fetched and stored.');
        } else {
            $this->warn('No lead data found from API.');
        }
    }
}
