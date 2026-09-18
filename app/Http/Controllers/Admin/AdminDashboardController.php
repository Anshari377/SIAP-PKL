<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        Application::syncCompletedApplications();

        $divisionQuery = $request->user()->agency_id
            ? Division::where('agency_id', $request->user()->agency_id)
            : Division::whereNull('agency_id');
        $applicationBaseQuery = $request->user()->agency_id
            ? Application::whereHas('division', fn ($query) => $query->where('agency_id', $request->user()->agency_id))
            : Application::whereHas('division', fn ($query) => $query->whereNull('agency_id'));
        $applicationQuery = $applicationBaseQuery->excludeDummy();

        return Inertia::render('Admin/Dashboard', [
            'activeNav' => 'admin.dashboard',
            'stats' => [
                'total_bidang' => (clone $divisionQuery)->count(),
                'pengajuan_baru' => (clone $applicationQuery)->where('status', 'pending')->count(),
                'menunggu_verifikasi' => (clone $applicationQuery)->where('status', 'pending')->count(),
                'peserta_aktif' => (clone $applicationQuery)->currentlyActive()->count(),
            ],
            'pengajuanTerbaru' => (clone $applicationQuery)
                ->with(['user', 'division'])
                ->latest()
                ->limit(5)
                ->get(),
            'bidangAktif' => (clone $divisionQuery)
                ->with(['applications' => fn ($query) => $query->currentlyActive()->excludeDummy()->withCount('members')])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function (Division $div) {
                    $terisi = $div->applications->sum(fn ($app) => max(1, $app->members_count ?? 1));
                    $div->terisi = min((int) $div->quota, $terisi);
                    return $div;
                }),
        ]);
    }
}