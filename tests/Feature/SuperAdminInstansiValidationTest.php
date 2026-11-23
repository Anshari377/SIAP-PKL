<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminInstansiValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

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
    }

    public function test_super_admin_cannot_create_instansi_without_nama(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.instansi.store'), [
                'nama' => '',
                'tipe' => 'pemerintah',
                'alamat' => 'Jl. Penjernihan No. 1',
                'maps_link' => 'https://maps.google.com/?q=disdik',
                'email' => 'disdik@kaltimprov.go.id',
            ]);

        $response->assertSessionHasErrors(['nama' => 'Nama instansi wajib diisi.']);
    }

    public function test_super_admin_cannot_create_instansi_without_maps_link(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.instansi.store'), [
                'nama' => 'Dinas Pendidikan Kaltim',
                'tipe' => 'pemerintah',
                'alamat' => 'Jl. Penjernihan No. 1',
                'maps_link' => '',
                'email' => 'disdik@kaltimprov.go.id',
            ]);

        $response->assertSessionHasErrors(['maps_link' => 'Tautan peta (Google Maps) instansi wajib diisi.']);
    }

    public function test_super_admin_cannot_create_instansi_without_email(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.instansi.store'), [
                'nama' => 'Dinas Pendidikan Kaltim',
                'tipe' => 'pemerintah',
                'alamat' => 'Jl. Penjernihan No. 1',
                'maps_link' => 'https://maps.google.com/?q=disdik',
                'email' => '',
            ]);

        $response->assertSessionHasErrors(['email' => 'Email instansi wajib diisi.']);
    }

    public function test_super_admin_can_create_instansi_with_all_required_fields(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('superadmin.instansi.store'), [
                'nama' => 'Dinas Pendidikan Kaltim',
                'tipe' => 'pemerintah',
                'alamat' => 'Jl. Penjernihan No. 1',
                'maps_link' => 'https://maps.google.com/?q=disdik',
                'email' => 'disdik@kaltimprov.go.id',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('agencies', [
            'name' => 'Dinas Pendidikan Kaltim',
            'contact_email' => 'disdik@kaltimprov.go.id',
            'maps_link' => 'https://maps.google.com/?q=disdik',
        ]);
    }

    public function test_super_admin_cannot_update_instansi_without_required_fields(): void
    {
        $agency = Agency::create([
            'name' => 'Dinas Kominfo Kaltim',
            'type' => 'government',
            'maps_link' => 'https://maps.google.com/?q=diskominfo',
            'contact_email' => 'diskominfo@kaltimprov.go.id',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->put(route('superadmin.instansi.update', $agency->id), [
                'nama' => 'Dinas Kominfo Kaltim',
                'tipe' => 'pemerintah',
                'maps_link' => '',
                'email' => 'diskominfo@kaltimprov.go.id',
            ]);

        $response->assertSessionHasErrors(['maps_link' => 'Tautan peta (Google Maps) instansi wajib diisi.']);
    }
}
