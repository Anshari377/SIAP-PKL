<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = $this->queryFor($request->user())
            ->with(['user.agency', 'division'])
            ->latest()
            ->get();

        return Inertia::render('Admin/Pengajuan/Index', [
            'activeNav' => 'admin.pengajuan',
            'pengajuan' => $applications,
        ]);
    }

    public function show(Request $request, Application $pengajuan)
    {
        $application = $this->queryFor($request->user())
            ->with(['user.agency', 'division.positions', 'members'])
            ->findOrFail($pengajuan->id);

        return Inertia::render('Admin/Pengajuan/Show', [
            'activeNav' => 'admin.pengajuan',
            'pengajuan' => $application,
        ]);
    }

    public function updateStatus(Request $request, Application $pengajuan)
    {
        $application = $this->queryFor($request->user())->with('division')->findOrFail($pengajuan->id);
        $data = $request->validate(['status' => ['required', 'in:accepted,rejected']]);

        DB::transaction(function () use ($application, $data) {
            $application->update(['status' => $data['status']]);
        });

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    public function document(Request $request, Application $pengajuan)
    {
        $application = $this->queryFor($request->user())->findOrFail($pengajuan->id);

        abort_unless($application->document_path && Storage::disk('public')->exists($application->document_path), 404);

        return response()->file(Storage::disk('public')->path($application->document_path), [
            'Content-Type' => Storage::disk('public')->mimeType($application->document_path) ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($application->document_path).'"',
        ]);
    }

    public function participants(Request $request)
    {
        $applications = $this->queryFor($request->user())
            ->where('status', 'accepted')
            ->with(['user.agency', 'division', 'members'])
            ->latest()
            ->get();

        $participants = $applications->flatMap(function (Application $application) {
            $members = $application->members;

            if ($members->isEmpty()) {
                return [[
                    'id' => "application-{$application->id}",
                    'nama' => $application->user?->name ?? '-',
                    'nim' => '-',
                    'instansi' => $application->user?->agency?->name ?? '-',
                    'bidang' => $application->division?->nama ?? '-',
                    'posisi' => 'Tidak ditentukan',
                    'tanggal_mulai' => $application->start_date?->toDateString(),
                    'tanggal_selesai' => $application->end_date?->toDateString(),
                    'status' => $application->end_date?->isPast() ? 'completed' : 'accepted',
                ]];
            }

            return $members->map(fn ($member) => [
                'id' => $member->id,
                'nama' => $member->name,
                'nim' => $member->nim ?? '-',
                'instansi' => $member->school,
                'bidang' => $application->division?->nama ?? '-',
                'posisi' => 'Tidak ditentukan',
                'tanggal_mulai' => $application->start_date?->toDateString(),
                'tanggal_selesai' => $application->end_date?->toDateString(),
                'status' => $application->end_date?->isPast() ? 'completed' : 'accepted',
            ]);
        })->values();

        $newThisMonth = $applications->filter(fn (Application $application) => $application->created_at?->isCurrentMonth())->count();

        return Inertia::render('Admin/Peserta/Index', [
            'activeNav' => 'admin.peserta',
            'peserta' => $participants,
            'stats' => [
                'total_aktif' => $participants->where('status', 'accepted')->count(),
                'selesai' => $participants->where('status', 'completed')->count(),
                'baru_bulan_ini' => $newThisMonth,
            ],
        ]);
    }

    private function queryFor($user)
    {
        return $user->agency_id
            ? Application::query()->whereHas('division', fn ($query) => $query->where('agency_id', $user->agency_id))
            : Application::query()->whereHas('division', fn ($query) => $query->whereNull('agency_id'));
    }
}