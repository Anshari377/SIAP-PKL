<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = [
            [
                'slug' => 'aplikasi-layanan-e-government',
                'nama' => 'Aplikasi dan Layanan E-Government',
                'kategori' => 'Teknologi Informasi',
                'instansi' => 'Dinas Komunikasi dan Informatika Kaltim',
                'quota' => 8,
                'deskripsi' => 'Bertanggung jawab atas pengembangan, pemeliharaan, dan peningkatan layanan aplikasi digital serta sistem elektronik pemerintahan (e-Government) untuk mendukung pelayanan publik yang efisien dan transparan.',
            ],
            [
                'slug' => 'sekretariat',
                'nama' => 'Sekretariat',
                'kategori' => 'Administrasi',
                'instansi' => 'Dinas Komunikasi dan Informatika Kaltim',
                'quota' => 4,
                'deskripsi' => 'Menangani administrasi umum, kepegawaian, tata usaha, dan dukungan operasional internal dinas.',
            ],
            [
                'slug' => 'infrastruktur-jaringan-dan-server',
                'nama' => 'Infrastruktur Jaringan dan Server',
                'kategori' => 'Infrastruktur Jaringan',
                'instansi' => 'Dinas Komunikasi dan Informatika Kaltim',
                'quota' => 6,
                'deskripsi' => 'Mengelola infrastruktur jaringan, server pusat data, dan layanan cloud milik instansi agar tetap stabil, aman, dan tersedia bagi seluruh perangkat daerah di Kalimantan Timur.',
            ],
            [
                'slug' => 'keamanan-siber',
                'nama' => 'Keamanan Siber',
                'kategori' => 'Keamanan Siber',
                'instansi' => 'Dinas Komunikasi dan Informatika Kaltim',
                'quota' => 5,
                'deskripsi' => 'Melindungi sistem dan data pemerintahan dari ancaman siber melalui pemantauan keamanan, pengujian kerentanan, dan penanganan insiden keamanan (CSIRT).',
            ],
            [
                'slug' => 'administrasi-kesiswaan',
                'nama' => 'Administrasi Kesiswaan',
                'kategori' => 'Administrasi',
                'instansi' => 'Dinas Pendidikan dan Kebudayaan Kaltim',
                'quota' => 4,
                'deskripsi' => 'Mendukung pengelolaan data siswa, surat-menyurat kesiswaan, dan administrasi penerimaan peserta didik baru di lingkungan Dinas Pendidikan Kalimantan Timur.',
            ],
            [
                'slug' => 'pengembangan-kurikulum-digital',
                'nama' => 'Pengembangan Kurikulum Digital',
                'kategori' => 'Pendidikan',
                'instansi' => 'Dinas Pendidikan dan Kebudayaan Kaltim',
                'quota' => 6,
                'deskripsi' => 'Menyusun dan mengembangkan materi pembelajaran digital serta platform pendidikan (e-learning) untuk mendukung implementasi Kurikulum Merdeka di sekolah-sekolah.',
            ],
            [
                'slug' => 'rekam-medis-digital',
                'nama' => 'Rekam Medis Digital',
                'kategori' => 'Kesehatan',
                'instansi' => 'RSUD Abdul Wahab Sjahranie',
                'quota' => 5,
                'deskripsi' => 'Mengelola digitalisasi rekam medis pasien, keakuratan data kesehatan, dan kepatuhan terhadap regulasi kerahasiaan serta keamanan informasi medis.',
            ],
            [
                'slug' => 'sistem-informasi-rumah-sakit',
                'nama' => 'Sistem Informasi Rumah Sakit',
                'kategori' => 'Kesehatan',
                'instansi' => 'RSUD Abdul Wahab Sjahranie',
                'quota' => 4,
                'deskripsi' => 'Mengembangkan dan memelihara sistem informasi rumah sakit (SIMRS) yang mencakup pendaftaran, farmasi, poliklinik, dan pelaporan rumah sakit.',
            ],
            [
                'slug' => 'analisis-data-pembangunan',
                'nama' => 'Analisis Data Pembangunan',
                'kategori' => 'Perencanaan',
                'instansi' => 'Badan Perencanaan Pembangunan Daerah (Bappeda)',
                'quota' => 6,
                'deskripsi' => 'Mengolah dan menganalisis data indikator pembangunan daerah untuk mendukung perencanaan, pengendalian, dan evaluasi program pembangunan.',
            ],
            [
                'slug' => 'sistem-informasi-geografis',
                'nama' => 'Sistem Informasi Geografis',
                'kategori' => 'Sistem Informasi Geografis',
                'instansi' => 'Badan Perencanaan Pembangunan Daerah (Bappeda)',
                'quota' => 5,
                'deskripsi' => 'Membangun dan memelihara data spasial serta peta digital untuk mendukung penataan ruang dan perencanaan pembangunan daerah berbasis lokasi.',
            ],
            [
                'slug' => 'teknologi-informasi-dan-perbankan-digital',
                'nama' => 'Teknologi Informasi dan Perbankan Digital',
                'kategori' => 'Teknologi Informasi',
                'instansi' => 'Bankaltimtara',
                'quota' => 5,
                'deskripsi' => 'Mengelola pengembangan layanan perbankan digital, aplikasi mobile banking, dan keamanan jaringan transaksi perbankan.',
            ],
            [
                'slug' => 'layanan-keuangan-dan-operasional',
                'nama' => 'Layanan Keuangan dan Operasional Perbankan',
                'kategori' => 'Keuangan',
                'instansi' => 'Bankaltimtara',
                'quota' => 4,
                'deskripsi' => 'Mendukung administrasi transaksi keuangan, pelayanan nasabah digital, dan analisis operasional perbankan.',
            ],
        ];

        foreach ($divisions as $division) {
            Division::updateOrCreate(
                ['slug' => $division['slug']],
                $division
            );
        }
    }
}