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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->uuid('Id')->primary();
            $table->uuid('lead_id');
            $table->integer('EventCode')->nullable();
            $table->string('EventName')->nullable();
            $table->integer('ActivityScore')->nullable();
            $table->timestamp('CreatedOn')->nullable();
            $table->integer('ActivityType')->nullable();
            $table->string('Type')->nullable();
            $table->boolean('IsEmailType')->nullable();
            $table->uuid('SessionId')->nullable();
            $table->integer('TrackLocation')->nullable();
            $table->decimal('Latitude', 10, 7)->nullable();
            $table->decimal('Longitude', 10, 7)->nullable();
            $table->uuid('RelatedProspectId')->nullable();
            $table->json('Data')->nullable();
            $table->json('ActivityFields')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->timestamps();
            $table->foreign('lead_id')->references('ProspectID')->on('leads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
