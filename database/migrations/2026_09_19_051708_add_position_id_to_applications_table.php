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
        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('division_id')->constrained('positions')->nullOnDelete();
        });

        // Backfill existing applications with the first position matching their division
        $applications = \Illuminate\Support\Facades\DB::table('applications')->whereNull('position_id')->get();
        foreach ($applications as $app) {
            $firstPositionId = \Illuminate\Support\Facades\DB::table('positions')
                ->where('division_id', $app->division_id)
                ->orderBy('id')
                ->value('id');

            if ($firstPositionId) {
                \Illuminate\Support\Facades\DB::table('applications')
                    ->where('id', $app->id)
                    ->update(['position_id' => $firstPositionId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};
