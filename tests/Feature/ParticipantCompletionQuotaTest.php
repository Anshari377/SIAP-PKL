<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Application;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParticipantCompletionQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_participant_reduces_occupied_quota_in_admin_bidang(): void
    {
        Role::findOrCreate('agency_admin');

        $agency = Agency::create(['nama' => 'Diskominfo Samarinda', 'slug' => 'diskominfo', 'kode' => 'DK']);

        $admin = User::create([
            'name' => 'Admin PKL',
            'email' => 'admin@pkl.test',
            'password' => bcrypt('password'),
            'agency_id' => $agency->id,
        ]);
        $admin->assignRole('agency_admin');

        $division = Division::create([
            'agency_id' => $agency->id,
            'slug' => 'sekretariat',
            'nama' => 'Sekretariat',
            'kategori' => 'Umum',
            'instansi' => 'Diskominfo Samarinda',
            'deskripsi' => 'Bidang sekretariat',
            'quota' => 6,
        ]);

        // 3 active participants (end_date in future)
        for ($i = 1; $i <= 3; $i++) {
            $user = User::factory()->create(['agency_id' => $agency->id]);
            Application::create([
                'user_id' => $user->id,
                'division_id' => $division->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonth()->toDateString(),
                'status' => 'accepted',
                'consent_pdp' => true,
            ]);
        }

        // 2 participants with end_date today (past time)
        for ($i = 4; $i <= 5; $i++) {
            $user = User::factory()->create(['agency_id' => $agency->id]);
            Application::create([
                'user_id' => $user->id,
                'division_id' => $division->id,
                'start_date' => now()->subMonth()->toDateString(),
                'end_date' => now()->toDateString(),
                'status' => 'accepted',
                'consent_pdp' => true,
            ]);
        }

        // Access /admin/peserta
        $resPeserta = $this->actingAs($admin)->get(route('admin.peserta.index'));
        $resPeserta->assertOk();

        $stats = $resPeserta->inertiaProps('stats');
        $this->assertEquals(3, $stats['total_aktif']);
        $this->assertEquals(2, $stats['selesai']);

        // Access /admin/bidang - quota must be 3/6, NOT 5/6!
        $resBidang = $this->actingAs($admin)->get(route('admin.bidang.index'));
        $resBidang->assertOk();

        $divisions = $resBidang->inertiaProps('divisions');
        $divData = collect($divisions)->firstWhere('id', $division->id);

        $this->assertEquals(3, $divData['terisi_total']);
        $this->assertEquals(6, $divData['kuota_total']);
    }

    public function test_admin_can_manually_complete_active_participant_and_quota_is_freed(): void
    {
        Role::findOrCreate('agency_admin');

        $agency = Agency::create(['nama' => 'Diskominfo Samarinda', 'slug' => 'diskominfo', 'kode' => 'DK']);

        $admin = User::create([
            'name' => 'Admin PKL',
            'email' => 'admin2@pkl.test',
            'password' => bcrypt('password'),
            'agency_id' => $agency->id,
        ]);
        $admin->assignRole('agency_admin');

        $division = Division::create([
            'agency_id' => $agency->id,
            'slug' => 'sekretariat-2',
            'nama' => 'Sekretariat 2',
            'kategori' => 'Umum',
            'instansi' => 'Diskominfo Samarinda',
            'deskripsi' => 'Bidang sekretariat 2',
            'quota' => 6,
        ]);

        $activeUser = User::factory()->create(['agency_id' => $agency->id]);
        $activeApp = Application::create([
            'user_id' => $activeUser->id,
            'division_id' => $division->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'accepted',
            'consent_pdp' => true,
        ]);

        // Verify active application occupies 1 slot
        $resBefore = $this->actingAs($admin)->get(route('admin.bidang.index'));
        $divDataBefore = collect($resBefore->inertiaProps('divisions'))->firstWhere('id', $division->id);
        $this->assertEquals(1, $divDataBefore['terisi_total']);

        // Complete the active application via PATCH route
        $completeRes = $this->actingAs($admin)->patch(route('admin.peserta.complete', $activeApp->id));
        $completeRes->assertSessionHas('success');

        $activeApp->refresh();
        $this->assertEquals('completed', $activeApp->status);

        // Verify quota is freed: terisi_total must now be 0
        $resAfter = $this->actingAs($admin)->get(route('admin.bidang.index'));
        $divDataAfter = collect($resAfter->inertiaProps('divisions'))->firstWhere('id', $division->id);
        $this->assertEquals(0, $divDataAfter['terisi_total']);
    }
}
