<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::latest()->get()->map(fn ($log) => [
            'id'        => $log->id,
            'waktu'     => $log->created_at->format('Y-m-d H:i:s'),
            'user'      => $log->user_nama,
            'aksi'      => $log->aksi,
            'instansi'  => $log->instansi,
            'ip'        => $log->ip_address,
            'perubahan' => $log->perubahan ?? [],
        ]);

        return Inertia::render('SuperAdmin/AuditLog/Index', [
            'logList' => $logs,
        ]);
    }
}