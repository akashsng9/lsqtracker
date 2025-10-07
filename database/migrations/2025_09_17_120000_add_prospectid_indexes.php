<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ensure leads.ProspectID has a unique index for efficient upserts
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                // Add length if needed based on your column definition (e.g., string(36))
                // Create unique index only if it doesn't already exist
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes($sm->getDatabasePlatform()->quoteSingleIdentifier('leads'));
                if (!array_key_exists('leads_prospectid_unique', $indexes) && !array_key_exists('prospectid_unique', $indexes)) {
                    $table->unique('ProspectID', 'leads_prospectid_unique');
                }
            });
        }

        // Add a standard index on team_configs.ProspectID to speed source scanning
        if (Schema::hasTable('team_configs')) {
            Schema::table('team_configs', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes($sm->getDatabasePlatform()->quoteSingleIdentifier('team_configs'));
                if (!array_key_exists('team_configs_prospectid_index', $indexes) && !array_key_exists('prospectid_index', $indexes)) {
                    $table->index('ProspectID', 'team_configs_prospectid_index');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'ProspectID')) {
                    try { $table->dropUnique('leads_prospectid_unique'); } catch (\Throwable $e) {}
                }
            });
        }
        if (Schema::hasTable('team_configs')) {
            Schema::table('team_configs', function (Blueprint $table) {
                if (Schema::hasColumn('team_configs', 'ProspectID')) {
                    try { $table->dropIndex('team_configs_prospectid_index'); } catch (\Throwable $e) {}
                }
            });
        }
    }
};
