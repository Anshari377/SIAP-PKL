<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = Division::all()->keyBy('slug');

        $data = [
            'aplikasi-layanan-e-government' => [
                [
                    'nama' => 'Web Developer',
                    'kuota' => 5,
                    'terisi' => 5,
                    'deskripsi' => 'Membantu tim mengembangkan dan memelihara aplikasi internal serta layanan publik berbasis web milik Diskominfo Samarinda.',
                    'kualifikasi' => ['Menguasai HTML/CSS/JS dasar', 'Familiar dengan salah satu framework backend', 'Mampu bekerja dalam tim'],
                    'jurusan' => ['Informatika', 'Rekayasa Perangkat Lunak (RPL)', 'Sistem Informasi', 'TKJ'],
                ],
                [
                    'nama' => 'UI/UX Designer',
                    'kuota' => 3,
                    'terisi' => 2,
                    'deskripsi' => 'Merancang wireframe dan antarmuka untuk layanan publik digital agar mudah digunakan masyarakat.',
                    'kualifikasi' => ['Menguasai Figma', 'Memahami prinsip UX dasar'],
                    'jurusan' => ['Desain Komunikasi Visual (DKV)', 'Informatika', 'Multimedia'],
                ],
            ],
            'sekretariat' => [
                [
                    'nama' => 'Administrasi',
                    'kuota' => 4,
                    'terisi' => 0,
                    'deskripsi' => 'Membantu pengelolaan surat-menyurat, kearsipan, dan administrasi umum di lingkungan Sekretariat Diskominfo Samarinda.',
                    'kualifikasi' => ['Teliti dan rapi dalam pengarsipan', 'Menguasai Microsoft Office dasar'],
                    'jurusan' => ['Administrasi Perkantoran', 'Manajemen', 'Semua jurusan (terbuka umum)'],
                ],
            ],
            'infrastruktur-jaringan-dan-server' => [
                [
                    'nama' => 'Network Engineer',
                    'kuota' => 3,
                    'terisi' => 1,
                    'deskripsi' => 'Membantu pemasangan, konfigurasi, dan pemantauan jaringan intranet/WiFi serta pendokumentasian topologi jaringan.',
                    'kualifikasi' => ['Memahami dasar routing dan switching', 'Dasar subnetting dan VLAN', 'Teliti dalam mendokumentasikan'],
                    'jurusan' => ['Teknik Komputer dan Jaringan (TKJ)', 'Informatika', 'Sistem Informasi'],
                ],
                [
                    'nama' => 'Cloud & Server Admin',
                    'kuota' => 3,
                    'terisi' => 1,
                    'deskripsi' => 'Membantu pengelolaan server virtual, backup data, dan layanan email/DNS instansi.',
                    'kualifikasi' => ['Dasar sistem operasi Linux/Windows Server', 'Memahami konsep virtualisasi', 'Bersedia belajar mandiri'],
                    'jurusan' => ['Informatika', 'Teknik Komputer dan Jaringan (TKJ)', 'Sistem Informasi'],
                ],
            ],
            'keamanan-siber' => [
                [
                    'nama' => 'Security Analyst',
                    'kuota' => 5,
                    'terisi' => 3,
                    'deskripsi' => 'Membantu pemantauan keamanan sistem, analisis log, dan pengujian kerentanan dasar (pentest) pada aplikasi instansi.',
                    'kualifikasi' => ['Memahami dasar jaringan dan keamanan', 'Familiar dengan Linux CLI', 'Etis dan menjaga kerahasiaan'],
                    'jurusan' => ['Informatika', 'Keamanan Jaringan', 'Teknik Komputer dan Jaringan (TKJ)'],
                ],
            ],
            'administrasi-kesiswaan' => [
                [
                    'nama' => 'Staf Administrasi Kesiswaan',
                    'kuota' => 4,
                    'terisi' => 4,
                    'deskripsi' => 'Membantu pendataan siswa, pengelolaan dokumen kesiswaan, dan pelayanan administrasi kepada sekolah.',
                    'kualifikasi' => ['Menguasai Microsoft Office/Spreadsheet', 'Teliti dan rapi', 'Komunikatif'],
                    'jurusan' => ['Administrasi Perkantoran', 'Manajemen', 'Semua jurusan (terbuka umum)'],
                ],
            ],
            'pengembangan-kurikulum-digital' => [
                [
                    'nama' => 'Pengembang Konten Pembelajaran',
                    'kuota' => 6,
                    'terisi' => 1,
                    'deskripsi' => 'Membantu pembuatan modul digital, video pembelajaran, dan perangkat LMS untuk mendukung Kurikulum Merdeka.',
                    'kualifikasi' => ['Kemampuan dasar menulis dan desain', 'Familiar dengan Canva/CapCut', 'Kreatif'],
                    'jurusan' => ['Pendidikan', 'Desain Komunikasi Visual (DKV)', 'Multimedia', 'Informatika'],
                ],
            ],
            'rekam-medis-digital' => [
                [
                    'nama' => 'Admin Rekam Medis Digital',
                    'kuota' => 5,
                    'terisi' => 3,
                    'deskripsi' => 'Membantu digitalisasi berkas rekam medis, verifikasi data pasien, dan menjaga kerahasiaan informasi kesehatan.',
                    'kualifikasi' => ['Teliti dan menjaga kerahasiaan', 'Menguasai komputer dasar', 'Bekerja cepat dan akurat'],
                    'jurusan' => ['Rekam Medis dan Informasi Kesehatan (RMIK)', 'Administrasi Kesehatan', 'Manajemen Informasi Kesehatan'],
                ],
            ],
            'sistem-informasi-rumah-sakit' => [
                [
                    'nama' => 'Analis Sistem Informasi RS',
                    'kuota' => 4,
                    'terisi' => 1,
                    'deskripsi' => 'Membantu pengujian, entri data, dan pelatihan pengguna SIMRS serta penyusunan pelaporan rutin rumah sakit.',
                    'kualifikasi' => ['Memahami dasar database/SQL', 'Komunikatif dan sabar', 'Menguasai Excel'],
                    'jurusan' => ['Sistem Informasi', 'Informatika', 'Rekam Medis dan Informasi Kesehatan (RMIK)'],
                ],
            ],
            'analisis-data-pembangunan' => [
                [
                    'nama' => 'Data Analyst',
                    'kuota' => 4,
                    'terisi' => 3,
                    'deskripsi' => 'Membantu pengumpulan, pembersihan, dan visualisasi data indikator pembangunan daerah.',
                    'kualifikasi' => ['Dasar statistika dan Excel lanjutan', 'Familiar dengan Python/Pandas atau R (nilai plus)', 'Analitis'],
                    'jurusan' => ['Statistika', 'Informatika', 'Ekonomi Pembangunan', 'Matematika'],
                ],
                [
                    'nama' => 'Staf Statistik Pendukung',
                    'kuota' => 2,
                    'terisi' => 1,
                    'deskripsi' => 'Membantu penyusunan laporan statistik dan pengolahan data hasil survei instansi.',
                    'kualifikasi' => ['Menguasai spreadsheet', 'Teliti', 'Dapat bekerja di bawah tenggat waktu'],
                    'jurusan' => ['Statistika', 'Matematika', 'Ekonomi Pembangunan'],
                ],
            ],
            'sistem-informasi-geografis' => [
                [
                    'nama' => 'Analis SIG',
                    'kuota' => 5,
                    'terisi' => 4,
                    'deskripsi' => 'Membantu pembuatan peta digital, digitasi data spasial, dan pemanfaatan GIS untuk perencanaan wilayah.',
                    'kualifikasi' => ['Dasar penggunaan QGIS/ArcGIS', 'Memahami koordinat dan proyeksi peta', 'Menguasai Excel'],
                    'jurusan' => ['Teknik Geomatika/Geodesi', 'Perencanaan Wilayah dan Kota (PWK)', 'Geografi'],
                ],
            ],
        ];

        foreach ($data as $slug => $positions) {
            $division = $divisions->get($slug);

            if (!$division) {
                continue;
            }

            foreach ($positions as $position) {
                Position::updateOrCreate(
                    ['division_id' => $division->id, 'nama' => $position['nama']],
                    $position
                );
            }
        }
    }
}