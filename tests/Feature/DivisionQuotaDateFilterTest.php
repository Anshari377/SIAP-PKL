<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DivisionQuotaDateFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_quota_calculation_with_date_range_overlap(): void
    {
        $division = Division::create([
            'slug' => 'test-bidang',
            'nama' => 'Teknologi Informasi',
            'kategori' => 'Umum',
            'instansi' => 'Diskominfo Samarinda',
            'deskripsi' => 'Deskripsi bidang IT',
            'quota' => 5,
        ]);

        $userPast = User::factory()->create();
        Application::create([
            'user_id' => $userPast->id,
            'division_id' => $division->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-03-01',
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        $userFuture = User::factory()->create();
        Application::create([
            'user_id' => $userFuture->id,
            'division_id' => $division->id,
            'start_date' => '2026-10-01',
            'end_date' => '2026-11-30',
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        // Scenario 1: Search for December 2026 (No overlap with any active application)
        $responseNoOverlap = $this->get(route('katalog.index', [
            'tanggal_mulai' => '2026-12-01',
            'tanggal_selesai' => '2026-12-31',
        ]));

        $responseNoOverlap->assertOk();
        $divisionsNoOverlap = $responseNoOverlap->inertiaProps('divisions');
        $divData1 = collect($divisionsNoOverlap)->firstWhere('id', $division->id);
        $this->assertEquals(0, $divData1['terisi_total']);
        $this->assertEquals(5, $divData1['sisa_total']);
        $this->assertEquals('tersedia', $divData1['status']);

        // Scenario 2: Search for October 2026 (Overlaps with $userFuture application)
        $responseOverlap = $this->get(route('katalog.index', [
            'tanggal_mulai' => '2026-10-15',
            'tanggal_selesai' => '2026-10-31',
        ]));

        $responseOverlap->assertOk();
        $divisionsOverlap = $responseOverlap->inertiaProps('divisions');
        $divData2 = collect($divisionsOverlap)->firstWhere('id', $division->id);
        $this->assertEquals(1, $divData2['terisi_total']);
        $this->assertEquals(4, $divData2['sisa_total']);
    }

    public function test_expired_accepted_application_does_not_consume_active_slot(): void
    {
        $division = Division::create([
            'slug' => 'expiring-bidang',
            'nama' => 'Administrasi',
            'kategori' => 'Umum',
            'instansi' => 'Diskominfo Samarinda',
            'deskripsi' => 'Deskripsi bidang administrasi',
            'quota' => 2,
        ]);

        $expiredUser = User::factory()->create();
        Application::create([
            'user_id' => $expiredUser->id,
            'division_id' => $division->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->subDays(2)->toDateString(),
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        $activeUser = User::factory()->create();
        Application::create([
            'user_id' => $activeUser->id,
            'division_id' => $division->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        $response = $this->get(route('katalog.index'));

        $response->assertOk();

        $divisions = $response->inertiaProps('divisions');
        $divData = collect($divisions)->firstWhere('id', $division->id);

        $this->assertEquals(1, $divData['terisi_total']);
        $this->assertEquals(1, $divData['sisa_total']);
        $this->assertEquals('menipis', $divData['status']);
    }

    public function test_completed_application_does_not_consume_active_slot_even_if_status_is_not_accepted(): void
    {
        $division = Division::create([
            'slug' => 'completed-bidang',
            'nama' => 'Humas',
            'kategori' => 'Umum',
            'instansi' => 'Diskominfo Samarinda',
            'deskripsi' => 'Deskripsi bidang humas',
            'quota' => 2,
        ]);

        $completedUser = User::factory()->create();
        Application::create([
            'user_id' => $completedUser->id,
            'division_id' => $division->id,
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => 'completed',
            'consent_pdp' => true,
        ]);

        $response = $this->get(route('katalog.index'));

        $response->assertOk();

        $divisions = $response->inertiaProps('divisions');
        $divData = collect($divisions)->firstWhere('id', $division->id);

        $this->assertEquals(0, $divData['terisi_total']);
        $this->assertEquals(2, $divData['sisa_total']);
    }
}
