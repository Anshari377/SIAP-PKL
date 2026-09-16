<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('is_walk_in')->default(false)->after('status');
            $table->index(['division_id', 'status', 'is_walk_in']);
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['division_id', 'status', 'is_walk_in']);
            $table->dropColumn('is_walk_in');
        });
    }
};