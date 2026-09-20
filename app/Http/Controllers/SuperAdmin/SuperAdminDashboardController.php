<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuperAdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $stats = [
            'total_instansi' => Agency::count(),
            'total_admin_aktif' => User::role('agency_admin')->count(),
            'total_pengajuan_systemwide' => Application::count(),
            'undangan_menunggu' => User::role('agency_admin')->whereNull('google_id')->count(),
        ];

        $aktivitasTerbaru = AuditLog::latest()->take(5)->get()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'waktu' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-',
                'user' => $log->user_nama ?? 'Sistem',
                'aksi' => $log->aksi,
                'instansi' => $log->instansi ?? '-',
            ];
        });

        return Inertia::render('SuperAdmin/Dashboard', [
            'activeNav' => 'superadmin.dashboard',
            'stats' => $stats,
            'aktivitasTerbaru' => $aktivitasTerbaru,
        ]);
    }
}
