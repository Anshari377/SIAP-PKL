<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminBidangOverrideTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Agency $agency;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'agency_admin']);
        Role::create(['name' => 'student']);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->agency = Agency::create([
            'name' => 'Diskominfo Kota Test',
            'type' => 'government',
            'address' => 'Jl. Test No. 123',
            'contact_email' => 'diskominfo@test.go.id',
        ]);
    }

    public function test_super_admin_can_create_bidang_for_instansi(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.instansi.bidang.store', $this->agency->id), [
                'nama' => 'Pengembangan Perangkat Lunak',
                'kategori' => 'Teknologi',
                'kuota_total' => 10,
                'jurusan' => 'Teknik Informatika, Sistem Informasi',
                'deskripsi' => 'Fokus pengembangan aplikasi web dan mobile.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('divisions', [
            'agency_id' => $this->agency->id,
            'nama' => 'Pengembangan Perangkat Lunak',
            'kategori' => 'Teknologi',
            'quota' => 10,
            'instansi' => 'Diskominfo Kota Test',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->superAdmin->id,
            'instansi' => 'Diskominfo Kota Test',
        ]);
    }

    public function test_super_admin_can_update_bidang_of_instansi(): void
    {
        $division = Division::create([
            'agency_id' => $this->agency->id,
            'slug' => 'jaringan-komputer',
            'nama' => 'Jaringan Komputer',
            'kategori' => 'Infrastruktur',
            'instansi' => $this->agency->name,
            'deskripsi' => 'Pengelolaan infrastruktur jaringan.',
            'quota' => 5,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.instansi.bidang.update', [$this->agency->id, $division->id]), [
                'nama' => 'Jaringan & Keamanan Siber',
                'kategori' => 'Infrastruktur & Keamanan',
                'kuota_total' => 8,
                'jurusan' => 'Teknik Komputer Jaringan, Cyber Security',
                'deskripsi' => 'Pengelolaan jaringan dan sistem keamanan siber.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
            'nama' => 'Jaringan & Keamanan Siber',
            'quota' => 8,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->superAdmin->id,
            'instansi' => $this->agency->name,
        ]);
    }

    public function test_super_admin_can_delete_bidang_without_applications(): void
    {
        $division = Division::create([
            'agency_id' => $this->agency->id,
            'slug' => 'humas-test',
            'nama' => 'Humas & Dokumentasi',
            'kategori' => 'Komunikasi',
            'instansi' => $this->agency->name,
            'deskripsi' => 'Pengelolaan kehumasan.',
            'quota' => 3,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.instansi.bidang.destroy', [$this->agency->id, $division->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('divisions', [
            'id' => $division->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->superAdmin->id,
            'aksi' => "Hapus Bidang PKL (Humas & Dokumentasi) di Instansi (Diskominfo Kota Test)",
        ]);
    }

    public function test_super_admin_cannot_delete_bidang_with_active_applications(): void
    {
        $division = Division::create([
            'agency_id' => $this->agency->id,
            'slug' => 'desain-grafis',
            'nama' => 'Desain Grafis',
            'kategori' => 'Kreatif',
            'instansi' => $this->agency->name,
            'deskripsi' => 'Desain media publikasi.',
            'quota' => 4,
        ]);

        $student = User::factory()->create();
        Application::create([
            'user_id' => $student->id,
            'division_id' => $division->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('superadmin.instansi.bidang.destroy', [$this->agency->id, $division->id]));

        $response->assertStatus(422);

        $this->assertDatabaseHas('divisions', [
            'id' => $division->id,
        ]);
    }
}
