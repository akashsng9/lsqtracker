<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('ProspectID')->primary();
            $table->string('ProspectAutoId')->nullable();
            $table->string('StatusCode')->nullable();
            $table->string('StatusReason')->nullable();
            $table->string('IsLead')->nullable();
            $table->string('NotableEvent')->nullable();
            $table->timestamp('NotableEventdate')->nullable();
            $table->timestamp('LastVisitDate')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('LastName')->nullable();
            $table->string('EmailAddress')->nullable();
            $table->string('Phone')->nullable();
            $table->string('Company')->nullable();
            $table->string('Source')->nullable();
            $table->string('SourceCampaign')->nullable();
            $table->string('JobTitle')->nullable();
            $table->string('Score')->nullable();
            $table->string('EngagementScore')->nullable();
            $table->string('ProspectStage')->nullable();
            $table->string('OwnerId')->nullable();
            $table->string('OwnerIdName')->nullable();
            $table->string('OwnerIdEmailAddress')->nullable();
            $table->string('CreatedBy')->nullable();
            $table->string('CreatedByName')->nullable();
            $table->timestamp('CreatedOn')->nullable();
            $table->timestamp('LeadConversionDate')->nullable();
            $table->string('ModifiedBy')->nullable();
            $table->string('ModifiedByName')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->string('mx_City')->nullable();
            $table->string('mx_Course_Interested')->nullable();
            $table->string('mx_Education_Completed')->nullable();
            $table->string('mx_Ad_Name')->nullable();
            $table->string('mx_campaign_Id')->nullable();
            $table->string('mx_Adset_Name')->nullable();
            $table->string('mx_Facebook_Form')->nullable();
            $table->string('mx_Facebook_Page')->nullable();
            $table->string('mx_Status')->nullable();
            $table->string('mx_Outcome')->nullable();
            $table->string('mx_Lead_Course')->nullable();
            $table->string('mx_AD_Network')->nullable();
            $table->string('mx_utm_creative_id')->nullable();
            $table->string('mx_FB_LeadGen_ID')->nullable();
            $table->string('mx_Program_Type')->nullable();
            $table->string('mx_Source_Category')->nullable();
            $table->string('mx_Courses_Category')->nullable();
            $table->string('mx_Total_Calls_in_Lead')->nullable();
            $table->string('mx_Level_of_interest')->nullable();
            $table->string('mx_Primary_reason_for_course')->nullable();
            $table->json('extra')->nullable(); // For all other fields
            // add other columns `mx_Total_Answered_Calls`, `Notes`, `mx_GCLID`, `mx_UTM_Term`, `mx_UTM_Source`, `mx_Country`, `mx_Lead_URL`, `SourceContent`, `SourceMedium`,
            $table->string('mx_Total_Answered_Calls')->nullable();
            $table->text('Notes')->nullable();
            $table->string('mx_GCLID')->nullable();
            $table->string('mx_UTM_Term')->nullable();
            $table->string('mx_UTM_Source')->nullable();
            $table->string('mx_Country')->nullable();
            $table->text('mx_Lead_URL')->nullable();
            $table->string('SourceContent')->nullable();
            $table->string('SourceMedium')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
