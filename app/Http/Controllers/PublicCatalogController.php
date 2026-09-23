<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithDivisions;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicCatalogController extends Controller
{
    use InteractsWithDivisions;

    public function index(Request $request)
    {
        $divisions = $this->divisions($request);

        $instansiList = $divisions->pluck('instansi')->unique()->sort()->values()->all();

        $stats = [
            'total_bidang' => $divisions->count(),
            'total_instansi' => count($instansiList),
            'total_slot_tersisa' => $divisions->sum('sisa_total'),
            'total_slot_terisi' => $divisions->sum('terisi_total'),
        ];

        $filters = $request->only('search', 'instansi', 'status', 'tanggal_mulai', 'tanggal_selesai');

        return Inertia::render('Katalog/Index', [
            'divisions' => $this->filterDivisions($divisions, $filters)->values(),
            'instansi' => $instansiList,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, string $division)
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        $division = Division::with([
                'positions',
                'agency',
                'applications' => fn ($query) => $this->applyAcceptedQuotaFilter($query, $tanggalMulai, $tanggalSelesai)->withCount('members'),
            ])
            ->where('slug', $division)
            ->firstOrFail();

        return Inertia::render('Katalog/Show', [
            'division' => $this->withQuota($division),
        ]);
    }
}