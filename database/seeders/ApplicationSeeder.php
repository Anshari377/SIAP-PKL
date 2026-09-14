<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationMember;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        // Reset data demo agar seeder dapat dijalankan berulang kali (idempotent).
        $oldIds = User::where('email', 'like', 'demo.%@pkl.test')->pluck('id');
        DB::table('model_has_roles')
            ->where('model_type', User::class)
            ->whereIn('model_id', $oldIds)
            ->delete();
        User::whereIn('id', $oldIds)->delete();

        // Jumlah pengajuan per status per bidang (accepted = slot terisi di katalog).
        $specs = [
            'aplikasi-layanan-e-government' => ['accepted' => 7, 'pending' => 1, 'rejected' => 1],
            'sekretariat' => ['accepted' => 0, 'pending' => 0, 'rejected' => 0],
            'infrastruktur-jaringan-dan-server' => ['accepted' => 2, 'pending' => 1, 'rejected' => 0],
            'keamanan-siber' => ['accepted' => 3, 'pending' => 1, 'rejected' => 1],
            'administrasi-kesiswaan' => ['accepted' => 4, 'pending' => 0, 'rejected' => 0],
            'pengembangan-kurikulum-digital' => ['accepted' => 1, 'pending' => 1, 'rejected' => 0],
            'rekam-medis-digital' => ['accepted' => 3, 'pending' => 0, 'rejected' => 1],
            'sistem-informasi-rumah-sakit' => ['accepted' => 1, 'pending' => 1, 'rejected' => 0],
            'analisis-data-pembangunan' => ['accepted' => 4, 'pending' => 0, 'rejected' => 0],
            'sistem-informasi-geografis' => ['accepted' => 4, 'pending' => 1, 'rejected' => 1],
        ];

        $schools = [
            'Politeknik Negeri Samarinda',
            'Universitas Mulawarman',
            'Universitas Teknologi Kalimantan',
            'SMK Negeri 1 Samarinda',
            'SMK Negeri 2 Balikpapan',
            'Politeknik Kesehatan Kaltim',
        ];

        $majors = [
            'Informatika', 'Rekayasa Perangkat Lunak', 'Sistem Informasi', 'Teknik Komputer dan Jaringan',
            'Administrasi Perkantoran', 'Manajemen', 'Desain Komunikasi Visual', 'Multimedia',
            'Rekam Medis dan Informasi Kesehatan', 'Statistika', 'Geografi', 'Pendidikan',
        ];

        foreach ($specs as $slug => $counts) {
            $division = Division::where('slug', $slug)->first();

            if (!$division) {
                continue;
            }

            foreach ($counts as $status => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $name = fake()->name();

                    $user = User::updateOrCreate(
                        ['email' => "demo.{$slug}.{$status}.{$i}@pkl.test"],
                        [
                            'name' => $name,
                            'password' => Hash::make('password'),
                            'email_verified_at' => now(),
                            'tipe_pendaftaran' => 'individu',
                        ]
                    );
                    $user->syncRoles(['student']);

                    $application = Application::updateOrCreate(
                        ['user_id' => $user->id, 'division_id' => $division->id],
                        [
                            'start_date' => now()->addDays(random_int(1, 14))->toDateString(),
                            'end_date' => now()->addMonths(3)->toDateString(),
                            'status' => $status,
                        ]
                    );

                    ApplicationMember::updateOrCreate(
                        ['application_id' => $application->id, 'name' => $name],
                        [
                            'school' => fake()->randomElement($schools),
                            'major' => fake()->randomElement($majors),
                            'phone' => '08' . fake()->numerify('##########'),
                        ]
                    );
                }
            }
        }
    }
}