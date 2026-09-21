<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan kolom school, major, dan phone nullable pada tabel application_members.
     *
     * Latar belakang: Form anggota kelompok hanya memiliki field name, nim, dan phone.
     * school dan major hanya ada pada form ketua, sehingga kolom ini harus nullable
     * agar anggota kelompok dapat disimpan tanpa nilai school dan major.
     */
    public function up(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            $table->string('school')->nullable()->change();
            $table->string('major')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('application_members', function (Blueprint $table) {
            // Kembalikan ke NOT NULL dengan default kosong jika di-rollback
            $table->string('school')->nullable(false)->default('')->change();
            $table->string('major')->nullable(false)->default('')->change();
            $table->string('phone')->nullable(false)->default('')->change();
        });
    }
};
