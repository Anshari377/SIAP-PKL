<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatusPendaftaranController extends Controller
{
    public function index(Request $request)
    {
        Application::syncCompletedApplications();

        $pendaftaran = Application::with(['division', 'members'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->first();

        return Inertia::render('Status/Index', [
            'activeNav' => 'pengajuan',
            'pendaftaran' => $pendaftaran,
        ]);
    }
}
