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

        $pengajuanAktif = Application::with('division')
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'accepted', 'revision'])
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
                'judul' => $pengajuanAktif->division?->nama ?? 'Pengajuan PKL',
                'instansi' => $pengajuanAktif->division?->instansi ?? '-',
                'tanggal' => $pengajuanAktif->created_at?->toISOString(),
                'updated_at' => $pengajuanAktif->updated_at?->toISOString(),
                'status' => $pengajuanAktif->status,
            ] : null,
            'pengumuman' => [],
        ]);
    }
}
