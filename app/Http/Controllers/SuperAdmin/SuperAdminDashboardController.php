<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\PermohonanPkl;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class SuperAdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $stats = [
            'total_instansi' => Instansi::count(),
            'total_admin_aktif' => User::role('agency_admin')->count(),
            'total_pengajuan_systemwide' => PermohonanPkl::count(),
            'undangan_menunggu' => User::role('agency_admin')->whereNull('google_id')->count(),
        ];

        $aktivitasTerbaru = Activity::with(['causer.instansi'])->latest()->take(5)->get()->map(function ($log) {
            $instansiId = $log->properties['instansi_id'] ?? $log->causer?->id_instansi;
            $instansiName = $log->causer?->instansi?->nama_instansi
                ?? ($instansiId ? Instansi::find($instansiId)?->nama_instansi : '-');

            return [
                'id' => $log->id,
                'waktu' => $log->created_at?->format('Y-m-d H:i:s') ?? '-',
                'user' => $log->causer?->nama_lengkap ?? $log->causer?->email ?? 'Sistem',
                'aksi' => $log->description,
                'instansi' => $instansiName ?? '-',
            ];
        });

        return Inertia::render('SuperAdmin/Dashboard', [
            'activeNav' => 'superadmin.dashboard',
            'stats' => $stats,
            'aktivitasTerbaru' => $aktivitasTerbaru,
        ]);
    }
}
