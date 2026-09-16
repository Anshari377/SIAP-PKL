<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatusPendaftaranController extends Controller
{
    public function index(Request $request)
    {
        $pendaftaran = Application::with(['division', 'members'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        if ($pendaftaran) {
            $pendaftaran->setAttribute('surat_balasan_url', $pendaftaran->surat_balasan_url);
        }

        return Inertia::render('Status/Index', [
            'activeNav' => 'pengajuan',
            'pendaftaran' => $pendaftaran,
        ]);
    }
}
