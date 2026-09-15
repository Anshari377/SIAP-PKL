<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('consent_pdp')->default(false)->after('document_path');
            $table->text('catatan_revisi')->nullable()->after('consent_pdp');
            $table->string('status', 30)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['consent_pdp', 'catatan_revisi']);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending')->change();
        });
    }
};
