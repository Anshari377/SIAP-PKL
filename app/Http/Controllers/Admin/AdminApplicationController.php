<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected,revision'],
            'catatan_revisi' => ['required_if:status,revision', 'nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($application, $data) {
            $updateData = ['status' => $data['status']];
            if ($data['status'] === 'revision') {
                $updateData['catatan_revisi'] = $data['catatan_revisi'];
            }
            $application->update($updateData);
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

    public function walkInCreate(Request $request)
    {
        $divisions = $this->divisionQuery($request->user())
            ->orderBy('nama')
            ->get(['id', 'nama', 'quota']);

        return Inertia::render('Admin/Peserta/WalkIn', [
            'activeNav' => 'admin.peserta.walk-in',
            'divisions' => $divisions,
        ]);
    }

    public function walkInStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim' => ['nullable', 'string', 'max:50'],
            'school' => ['required', 'string', 'max:255'],
            'major' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $division = $this->divisionQuery($request->user())->findOrFail($data['division_id']);
        $occupied = Application::query()
            ->where('division_id', $division->id)
            ->where('status', 'accepted')
            ->where(function ($query) use ($data) {
                $query->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date']);
            })
            ->withCount('members')
            ->get()
            ->sum(fn (Application $application) => max(1, $application->members_count));

        abort_if($occupied >= $division->quota, 422, 'Kuota bidang penuh untuk periode tersebut.');

        DB::transaction(function () use ($data, $division, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => 'walkin+'.Str::uuid().'@pkl.local',
                'password' => Str::random(40),
                'agency_id' => $request->user()->agency_id,
                'tipe_pendaftaran' => 'individu',
            ]);

            $application = Application::create([
                'user_id' => $user->id,
                'division_id' => $division->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => 'accepted',
                'is_walk_in' => true,
                'consent_pdp' => true,
            ]);

            $application->members()->create([
                'name' => $data['name'],
                'nim' => $data['nim'] ?? null,
                'school' => $data['school'],
                'major' => $data['major'],
                'phone' => $data['phone'],
            ]);
        });

        return to_route('admin.peserta.index')->with('success', 'Peserta walk-in berhasil diregistrasikan.');
    }

    private function queryFor($user)
    {
        $query = $user->agency_id
            ? Application::query()->whereHas('division', fn ($query) => $query->where('agency_id', $user->agency_id))
            : Application::query()->whereHas('division', fn ($query) => $query->whereNull('agency_id'));

        return $query->excludeDummy();
    }

    private function divisionQuery($user)
    {
        return $user->agency_id
            ? Division::query()->where('agency_id', $user->agency_id)
            : Division::query()->whereNull('agency_id');
    }
}