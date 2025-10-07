<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadSearchLead extends Model
{
    use HasFactory;
    protected $table = 'lead_search_leads';
    protected $fillable = [
        'ProspectID','ProspectAutoId','IsLead','NotableEvent','NotableEventdate','ProspectActivityName_Max','ProspectActivityDate_Max','LeadLastModifiedOn','FirstName','LastName','EmailAddress','Phone','SourceCampaign','SourceMedium','SourceContent','Score','ProspectStage','OwnerId','CreatedByName','CreatedOn','LeadConversionDate','ModifiedBy','ModifiedByName','mx_Lead_URL','mx_City','mx_Country','OwnerIdName','OwnerIdEmailAddress','Origin','mx_Course_Interested','mx_Lead_Course','mx_Ad_Name','mx_campaign_Id','mx_Adset_Name','mx_UTM_Source','mx_UTM_Term','mx_Facebook_Form','mx_Facebook_Page','mx_Status','mx_Outcome','mx_GCLID','mx_Primary_reason_for_course','mx_utm_creative_id','mx_FB_LeadGen_ID','mx_Program_Type','mx_Source_Category','mx_Courses_Category','Notes','mx_Total_Calls_in_Lead','mx_Total_Answered_Calls','mx_Last_Follow_Up_Date','mx_Pre_Qualified_Leads','mx_Activity_Notes','mx_utm_medium','LeadAge','mx_Education_Completed','mx_QualityScore01','mx_Quality_Lead','mx_AD_Network','LastVisitDate',
        'IsStarredLead','IsTaggedLead','CanUpdate','raw'
    ];
}
