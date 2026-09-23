<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        Application::syncCompletedApplications();

        $riwayat = Application::with(['division', 'position'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (Application $item) {
                return [
                    'id' => $item->id,
                    'status' => $item->status,
                    'start_date' => $item->start_date?->toDateString(),
                    'end_date' => $item->end_date?->toDateString(),
                    'created_at' => $item->created_at,
                    'bidang' => $item->division?->nama ?? 'Data tidak tersedia',
                    'posisi' => $item->position?->nama ?? $item->division?->nama ?? 'Peserta PKL',
                    'instansi' => $item->division?->instansi ?? 'Data tidak tersedia',
                    'catatan_revisi' => $item->catatan_revisi,
                    'document_path' => $item->document_path,
                    'surat_balasan_url' => $item->surat_balasan_url,
                ];
            });

        return Inertia::render('Riwayat/Index', [
            'activeNav' => 'riwayat',
            'riwayat' => $riwayat,
        ]);
    }
}
