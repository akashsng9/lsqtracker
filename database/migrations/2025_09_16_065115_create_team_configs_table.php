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
        Schema::create('team_configs', function (Blueprint $table) {
            $table->id();
            $table->uuid('ProspectID')->unique();
            $table->bigInteger('ProspectAutoId')->nullable();
            $table->string('EmailAddress')->nullable();
            $table->string('Score')->nullable();
            $table->string('OwnerId')->unique();
            $table->timestamp('CreatedOn')->nullable();
            $table->boolean('IsStarredLead')->nullable();
            $table->boolean('CanUpdate')->nullable();
            $table->boolean('IsTaggedLead')->nullable();
             $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_configs');
    }
};
