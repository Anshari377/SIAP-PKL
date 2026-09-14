<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->json('jurusan')->nullable()->after('quota');
        });

        $positions = DB::table('positions')
            ->select('division_id', 'jurusan')
            ->whereNotNull('jurusan')
            ->get();

        $jurusanByDivision = $positions->groupBy('division_id')->map(function ($rows) {
            return collect($rows)
                ->flatMap(fn ($row) => json_decode($row->jurusan, true) ?? [])
                ->unique()
                ->values()
                ->all();
        });

        foreach ($jurusanByDivision as $divisionId => $jurusan) {
            DB::table('divisions')
                ->where('id', $divisionId)
                ->update(['jurusan' => $jurusan]);
        }
    }

    public function down(): void
    {
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropColumn('jurusan');
        });
    }
};