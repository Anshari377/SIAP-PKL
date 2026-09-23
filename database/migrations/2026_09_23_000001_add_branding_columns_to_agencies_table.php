<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->string('nama_singkat')->nullable()->after('name');
            $table->string('slug')->nullable()->unique()->after('nama_singkat');
            $table->string('logo')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['nama_singkat', 'slug', 'logo']);
        });
    }
};