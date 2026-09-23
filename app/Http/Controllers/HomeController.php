<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        Application::syncCompletedApplications();

        $pengajuanAktif = Application::with(['division', 'position'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'accepted', 'revision', 'completed'])
            ->latest()
            ->first();

        return Inertia::render('Home', [
            'activeNav' => 'home',
            'stats' => [
                'lowongan_tersedia' => Division::count(),
                'pendaftaran' => Application::where('user_id', $userId)->count(),
                'menunggu_verifikasi' => Application::where('user_id', $userId)
                    ->whereIn('status', ['pending', 'revision'])->count(),
                'diterima' => Application::where('user_id', $userId)->active()->count(),
            ],
            'pendaftaranAktif' => $pengajuanAktif ? [
                'id' => $pengajuanAktif->id,
                'judul' => $pengajuanAktif->position?->nama ?? $pengajuanAktif->division?->nama ?? 'Pengajuan PKL',
                'instansi' => $pengajuanAktif->division?->instansi ?? '-',
                'tanggal' => $pengajuanAktif->created_at?->toISOString(),
                'created_at' => $pengajuanAktif->created_at?->toISOString(),
                'updated_at' => $pengajuanAktif->updated_at?->toISOString(),
                'start_date' => $pengajuanAktif->start_date?->toDateString(),
                'end_date' => $pengajuanAktif->end_date?->toDateString(),
                'status' => $pengajuanAktif->status,
            ] : null,
            'pengumuman' => [],
        ]);
    }
}
