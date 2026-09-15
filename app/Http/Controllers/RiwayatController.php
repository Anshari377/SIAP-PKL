<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $riwayat = Application::with('division')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function (Application $item) {
                return [
                    'id' => $item->id,
                    'status' => $item->status,
                    'created_at' => $item->created_at?->format('d M Y'),
                    'bidang' => $item->division?->nama ?? 'Data tidak tersedia',
                    'posisi' => 'Peserta PKL',
                    'instansi' => $item->division?->instansi ?? 'Data tidak tersedia',
                    'catatan_revisi' => $item->catatan_revisi,
                    'document_path' => $item->document_path,
                ];
            });

        return Inertia::render('Riwayat/Index', [
            'activeNav' => 'riwayat',
            'riwayat' => $riwayat,
        ]);
    }
}
