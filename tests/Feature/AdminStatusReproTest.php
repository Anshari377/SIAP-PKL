<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Agency;
use App\Models\Application;
use App\Models\Division;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminStatusReproTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_status_with_surat_balasan_file(): void
    {
        Role::findOrCreate('student');
        Role::findOrCreate('agency_admin');

        $agency = Agency::create(['nama' => 'Diskominfo Kaltim', 'slug' => 'diskominfo-kaltim', 'kode' => 'DK']);
        $division = Division::create([
            'agency_id' => $agency->id,
            'slug' => 'aplikasi-layanan',
            'nama' => 'Aplikasi & Layanan',
            'kategori' => 'teknis',
            'instansi' => 'Diskominfo Kaltim',
            'deskripsi' => 'test',
            'quota' => 10,
        ]);

        $student = User::create([
            'name' => 'Mahasiswa',
            'email' => 'student@pkl.test',
            'password' => 'secret123',
            'agency_id' => $agency->id,
        ]);
        $student->assignRole('student');

        $application = Application::create([
            'user_id' => $student->id,
            'division_id' => $division->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'pending',
            'consent_pdp' => true,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@pkl.test',
            'password' => 'secret123',
            'agency_id' => $agency->id,
        ]);
        $admin->assignRole('agency_admin');

        Storage::fake('public');
        $pdf = UploadedFile::fake()->createWithContent(
            'surat.pdf',
            "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF"
        );

        $response = $this->actingAs($admin)->patch(
            route('admin.pengajuan.status', $application->id),
            ['status' => 'accepted', 'surat_balasan' => $pdf]
        );

        $response->dumpStatus();

        $application->refresh();
        dump('status after: ' . $application->status);
        dump('surat_balasan_path: ' . $application->surat_balasan_path);

        $this->assertEquals('accepted', $application->status);
        $this->assertNotNull($application->surat_balasan_path);
        Storage::disk('public')->assertExists($application->surat_balasan_path);
    }
}