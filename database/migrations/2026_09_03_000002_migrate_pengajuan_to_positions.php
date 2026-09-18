<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $divisions = [
            [
                'slug' => 'aplikasi-layanan-e-government',
                'nama' => 'Aplikasi dan Layanan E-Government',
                'kategori' => 'Teknologi Informasi',
                'instansi' => 'Dinas Komunikasi dan Informatika',
                'deskripsi' => 'Bertanggung jawab atas pengembangan, pemeliharaan, dan peningkatan layanan aplikasi digital serta sistem elektronik pemerintahan (e-Government) untuk mendukung pelayanan publik yang efisien dan transparan.',
            ],
            [
                'slug' => 'sekretariat',
                'nama' => 'Sekretariat',
                'kategori' => 'Administrasi',
                'instansi' => 'Dinas Komunikasi dan Informatika',
                'deskripsi' => 'Menangani administrasi umum, kepegawaian, tata usaha, dan dukungan operasional internal dinas.',
            ],
        ];

        foreach ($divisions as $division) {
            DB::table('divisions')->updateOrInsert(
                ['slug' => $division['slug']],
                [...$division, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        $divisionIds = DB::table('divisions')->pluck('id', 'slug');
        $positions = [
            [
                'division_id' => $divisionIds['aplikasi-layanan-e-government'],
                'nama' => 'Web Developer',
                'deskripsi' => 'Membantu tim mengembangkan dan memelihara aplikasi internal serta layanan publik berbasis web milik Diskominfo Samarinda.',
                'kuota' => 5,
                'terisi' => 5,
                'kualifikasi' => json_encode(['Menguasai HTML/CSS/JS dasar', 'Familiar dengan salah satu framework backend', 'Mampu bekerja dalam tim']),
                'jurusan' => json_encode(['Informatika', 'Rekayasa Perangkat Lunak (RPL)', 'Sistem Informasi', 'TKJ']),
            ],
            [
                'division_id' => $divisionIds['aplikasi-layanan-e-government'],
                'nama' => 'UI/UX Designer',
                'deskripsi' => 'Merancang wireframe dan antarmuka untuk layanan publik digital agar mudah digunakan masyarakat.',
                'kuota' => 3,
                'terisi' => 2,
                'kualifikasi' => json_encode(['Menguasai Figma', 'Memahami prinsip UX dasar']),
                'jurusan' => json_encode(['Desain Komunikasi Visual (DKV)', 'Informatika', 'Multimedia']),
            ],
            [
                'division_id' => $divisionIds['sekretariat'],
                'nama' => 'Administrasi',
                'deskripsi' => 'Membantu pengelolaan surat-menyurat, kearsipan, dan administrasi umum di lingkungan Sekretariat Diskominfo Samarinda.',
                'kuota' => 4,
                'terisi' => 0,
                'kualifikasi' => json_encode(['Teliti dan rapi dalam pengarsipan', 'Menguasai Microsoft Office dasar', 'Mampu berkomunikasi dengan baik']),
                'jurusan' => json_encode(['Administrasi Perkantoran', 'Manajemen', 'Semua jurusan (terbuka umum)']),
            ],
        ];

        foreach ($positions as $position) {
            DB::table('positions')->updateOrInsert(
                ['division_id' => $position['division_id'], 'nama' => $position['nama']],
                [...$position, 'created_at' => $now, 'updated_at' => $now]
            );
        }

        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('user_id')->constrained('positions')->nullOnDelete();
        });

        DB::table('pengajuan_pkl')
            ->join('lowongan', 'lowongan.id', '=', 'pengajuan_pkl.lowongan_id')
            ->select('pengajuan_pkl.id', 'lowongan.judul')
            ->orderBy('pengajuan_pkl.id')
            ->each(function ($application) {
                $positionId = DB::table('positions')
                    ->where('nama', $application->judul)
                    ->value('id');

                if ($positionId === null) {
                    throw new RuntimeException("Posisi untuk pengajuan {$application->id} tidak ditemukan.");
                }

                DB::table('pengajuan_pkl')
                    ->where('id', $application->id)
                    ->update(['position_id' => $positionId]);
            });

        if (DB::table('pengajuan_pkl')->whereNull('position_id')->exists()) {
            throw new RuntimeException('Tidak semua pengajuan lama berhasil dipetakan ke positions.');
        }

        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->dropForeign(['lowongan_id']);
            $table->dropColumn('lowongan_id');
            $table->foreignId('position_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->foreignId('lowongan_id')->nullable()->after('user_id')->constrained('lowongan')->nullOnDelete();
        });

        DB::table('pengajuan_pkl')
            ->join('positions', 'positions.id', '=', 'pengajuan_pkl.position_id')
            ->select('pengajuan_pkl.id', 'positions.nama')
            ->orderBy('pengajuan_pkl.id')
            ->each(function ($application) {
                $lowonganId = DB::table('lowongan')
                    ->where('judul', $application->nama)
                    ->value('id');

                DB::table('pengajuan_pkl')
                    ->where('id', $application->id)
                    ->update(['lowongan_id' => $lowonganId]);
            });

        Schema::table('pengajuan_pkl', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
            $table->foreignId('lowongan_id')->nullable(false)->change();
        });
    }
};
