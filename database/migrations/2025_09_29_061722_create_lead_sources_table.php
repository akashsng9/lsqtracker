<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_lead_source_master', function (Blueprint $table) {
            $table->id();
            $table->string('leadSource');
            $table->enum('sourceType', ['Paid', 'Organic'])->default('Organic');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Insert default values
        // DB::table('tbl_lead_source_master')->insert([
        //     ['leadSource' => 'Adwords', 'sourceType' => 'Paid', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'Facebook', 'sourceType' => 'Paid', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'Website', 'sourceType' => 'Organic', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'FB Lead Ads', 'sourceType' => 'Paid', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'Discovery', 'sourceType' => 'Paid', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'google', 'sourceType' => 'Paid', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'Inbound Phone call', 'sourceType' => 'Organic', 'createdAt' => now(), 'updated_at' => now()],
        //     ['leadSource' => 'Outbound Phone call', 'sourceType' => 'Organic', 'createdAt' => now(), 'updated_at' => now()],
        // ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_lead_source_master');
    }
};
