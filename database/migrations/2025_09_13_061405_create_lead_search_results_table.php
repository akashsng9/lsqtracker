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
        Schema::create('lead_search_results', function (Blueprint $table) {
            $table->id();
            $table->string('ListId')->nullable();
            $table->integer('PageIndex')->nullable();
            $table->integer('PageSize')->nullable();
            $table->json('result')->nullable(); // Store the full API response
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_search_results');
    }
};
