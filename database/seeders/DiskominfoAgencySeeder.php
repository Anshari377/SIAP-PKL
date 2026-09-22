<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Division;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiskominfoAgencySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $agency = Agency::updateOrCreate(
                ['name' => 'Dinas Komunikasi dan Informatika Kaltim'],
                [
                    'type' => 'government',
                    'address' => 'Jl. Kesuma Bangsa No. 12, Samarinda',
                    'maps_link' => 'https://maps.google.com/?q=Dinas+Komunikasi+dan+Informatika+Kaltim+Samarinda',
                    'contact_email' => 'admin@dkominfo.kaltimprov.go.id',
                    'description' => 'Instansi teknis Penyelenggara Pusat Data dan Layanan Informasi Pemerintah Provinsi Kalimantan Timur.',
                ]
            );

            $divisions = [
                [
                    'slug' => 'infrastruktur-jaringan-dan-server',
                    'nama' => 'Infrastruktur Jaringan dan Server',
                    'kategori' => 'Infrastruktur Jaringan',
                    'quota' => 6,
                    'jurusan' => null,
                    'deskripsi' => 'Mengelola infrastruktur jaringan, server pusat data, dan layanan cloud milik instansi.',
                ],
                [
                    'slug' => 'keamanan-siber',
                    'nama' => 'Keamanan Siber',
                    'kategori' => 'Keamanan Siber',
                    'quota' => 5,
                    'jurusan' => null,
                    'deskripsi' => 'Melindungi sistem dan data pemerintahan dari ancaman siber serta membantu penanganan insiden keamanan.',
                ],
                [
                    'slug' => 'aplikasi-layanan-e-government',
                    'nama' => 'Aplikasi dan Layanan E-Government',
                    'kategori' => 'Teknologi Informasi',
                    'quota' => 8,
                    'jurusan' => [
                        'Informatika',
                        'Rekayasa Perangkat Lunak (RPL)',
                        'Sistem Informasi',
                        'TKJ',
                        'Desain Komunikasi Visual (DKV)',
                        'Multimedia',
                    ],
                    'deskripsi' => 'Mengembangkan, memelihara, dan meningkatkan layanan aplikasi digital serta sistem elektronik pemerintahan.',
                ],
                [
                    'slug' => 'sekretariat',
                    'nama' => 'Sekretariat',
                    'kategori' => 'Administrasi',
                    'quota' => 4,
                    'jurusan' => [
                        'Administrasi Perkantoran',
                        'Manajemen',
                        'Semua jurusan (terbuka umum)',
                    ],
                    'deskripsi' => 'Menangani administrasi umum, kepegawaian, tata usaha, dan dukungan operasional internal dinas.',
                ],
            ];

            foreach ($divisions as $division) {
                Division::updateOrCreate(
                    ['slug' => $division['slug']],
                    [
                        'agency_id' => $agency->id,
                        'nama' => $division['nama'],
                        'kategori' => $division['kategori'],
                        'instansi' => $agency->name,
                        'deskripsi' => $division['deskripsi'],
                        'quota' => $division['quota'],
                        'jurusan' => $division['jurusan'],
                    ]
                );
            }

            User::where('email', 'ryuugami457@gmail.com')->update([
                'agency_id' => $agency->id,
            ]);
        });
    }
}