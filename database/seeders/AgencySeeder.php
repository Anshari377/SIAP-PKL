<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Division;
use Illuminate\Database\Seeder;

class AgencySeeder extends Seeder
{
    public function run(): void
    {
        $agencies = [
            [
                'name' => 'Dinas Komunikasi dan Informatika Kaltim',
                'type' => 'government',
                'address' => 'Jl. Kesuma Bangsa No. 12, Samarinda',
                'maps_link' => 'https://maps.google.com/?q=Dinas+Komunikasi+dan+Informatika+Kaltim+Samarinda',
                'contact_email' => 'admin@dkominfo.kaltimprov.go.id',
                'description' => 'Instansi teknis Penyelenggara Pusat Data & Layanan Informasi Pemerintah Provinsi Kalimantan Timur.',
                'match_keys' => ['Dinas Komunikasi dan Informatika', 'Dinas Komunikasi dan Informatika Kaltim', 'Diskominfo'],
                'slugs' => [
                    'aplikasi-layanan-e-government',
                    'sekretariat',
                    'infrastruktur-jaringan-dan-server',
                    'keamanan-siber',
                ],
            ],
            [
                'name' => 'Dinas Pendidikan dan Kebudayaan Kaltim',
                'type' => 'government',
                'address' => 'Jl. Bhayangkara No. 9, Samarinda',
                'maps_link' => 'https://maps.google.com/?q=Dinas+Pendidikan+dan+Kebudayaan+Kaltim',
                'contact_email' => 'disdik@kaltimprov.go.id',
                'description' => 'Instansi pemerintah yang membidangi urusan pendidikan dan kebudayaan di Kalimantan Timur.',
                'match_keys' => ['Dinas Pendidikan Kaltim', 'Dinas Pendidikan dan Kebudayaan Kaltim'],
                'slugs' => [
                    'administrasi-kesiswaan',
                    'pengembangan-kurikulum-digital',
                ],
            ],
            [
                'name' => 'RSUD Abdul Wahab Sjahranie',
                'type' => 'government',
                'address' => 'Jl. Palaran Ring Road I, Samarinda',
                'maps_link' => 'https://maps.google.com/?q=RSUD+Abdul+Wahab+Sjahranie+Samarinda',
                'contact_email' => 'humas@rsudaws.co.id',
                'description' => 'Rumah sakit rujukan utama milik Pemerintah Provinsi Kalimantan Timur.',
                'match_keys' => ['RSUD Abdul Wahab Sjahranie'],
                'slugs' => [
                    'rekam-medis-digital',
                    'sistem-informasi-rumah-sakit',
                ],
            ],
            [
                'name' => 'Badan Perencanaan Pembangunan Daerah (Bappeda)',
                'type' => 'government',
                'address' => 'Jl. Kesuma Bangsa No. 2, Samarinda',
                'maps_link' => 'https://maps.google.com/?q=Bappeda+Kaltim+Samarinda',
                'contact_email' => 'bappeda@kaltimprov.go.id',
                'description' => 'Badan perencanaan pembangunan daerah Provinsi Kalimantan Timur.',
                'match_keys' => ['Badan Perencanaan Pembangunan Daerah (Bappeda)'],
                'slugs' => [
                    'analisis-data-pembangunan',
                    'sistem-informasi-geografis',
                ],
            ],
            [
                'name' => 'Bankaltimtara',
                'type' => 'private',
                'address' => 'Jl. Jend. Sudirman No. 20, Samarinda',
                'maps_link' => 'https://maps.google.com/?q=Bankaltimtara+Samarinda',
                'contact_email' => 'cs@bankaltimtara.co.id',
                'description' => 'Bank Pembangunan Daerah Kalimantan Timur dan Kalimantan Utara.',
                'match_keys' => ['Bankaltimtara'],
                'slugs' => [
                    'teknologi-informasi-dan-perbankan-digital',
                    'layanan-keuangan-dan-operasional',
                ],
            ],
        ];

        // Jika agency 1 sudah ada bernama 'Diskominfo', update namanya agar selaras
        $firstAgency = Agency::find(1);
        if ($firstAgency && in_array(strtolower($firstAgency->name), ['diskominfo', 'dinas komunikasi dan informatika'])) {
            $firstAgency->update([
                'name' => 'Dinas Komunikasi dan Informatika Kaltim',
                'address' => 'Jl. Kesuma Bangsa No. 12, Samarinda',
                'description' => 'Instansi teknis Penyelenggara Pusat Data & Layanan Informasi Pemerintah Provinsi Kalimantan Timur.',
            ]);
        }

        foreach ($agencies as $agencyData) {
            $matchKeys = $agencyData['match_keys'];
            $slugs = $agencyData['slugs'];
            unset($agencyData['match_keys'], $agencyData['slugs']);

            $agency = Agency::updateOrCreate(
                ['name' => $agencyData['name']],
                $agencyData
            );

            // Update divisions berdasarkan slug atau nama instansi
            Division::query()
                ->whereIn('slug', $slugs)
                ->orWhereIn('instansi', $matchKeys)
                ->update([
                    'agency_id' => $agency->id,
                    'instansi' => $agency->name,
                ]);
        }
    }
}
