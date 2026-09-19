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
            ->with(['user.agency', 'division.positions', 'position', 'members'])
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
            ->with(['user.agency', 'division.positions', 'position', 'members'])
            ->findOrFail($pengajuan->id);

        return Inertia::render('Admin/Pengajuan/Show', [
            'activeNav' => 'admin.pengajuan',
            'pengajuan' => $application,
        ]);
    }

    public function updateStatus(Request $request, Application $pengajuan)
    {
        $application = $this->queryFor($request->user())->with('division')->findOrFail($pengajuan->id);

        abort_unless($application->status === 'pending', 422, 'Pengajuan ini sudah diproses.');

        $data = $request->validate([
            'status' => ['required', 'string', 'in:accepted,rejected,revision'],
            'catatan_revisi' => ['required_if:status,revision', 'nullable', 'string', 'max:1000'],
            'surat_balasan' => ['required_if:status,accepted', 'required_if:status,rejected', 'nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        if ($data['status'] === 'accepted') {
            Application::syncCompletedApplications();

            $division = $application->division;
            $startDate = $application->start_date?->toDateString();
            $endDate = $application->end_date?->toDateString();

            if ($division && $startDate && $endDate) {
                $occupied = Application::where('division_id', $division->id)
                    ->where('id', '!=', $application->id)
                    ->active()
                    ->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate)
                    ->withCount('members')
                    ->get()
                    ->sum(fn ($app) => max(1, $app->members_count));

                $needed = max(1, $application->members()->count());
                if ($occupied + $needed > $division->quota) {
                    return back()->withErrors([
                        'status' => "Kuota bidang {$division->nama} sudah penuh untuk periode tersebut (Terisi: {$occupied}/{$division->quota}).",
                    ]);
                }
            }
        }

        DB::transaction(function () use ($application, $data, $request) {
            $updateData = ['status' => $data['status']];
            if ($data['status'] === 'revision') {
                $updateData['catatan_revisi'] = $data['catatan_revisi'];
            } else {
                $updateData['catatan_revisi'] = null;
            }

            if ($request->hasFile('surat_balasan')) {
                if ($application->surat_balasan_path && Storage::disk('public')->exists($application->surat_balasan_path)) {
                    Storage::disk('public')->delete($application->surat_balasan_path);
                }

                $updateData['surat_balasan_path'] = $request->file('surat_balasan')
                    ->store('reply-letters', 'public');
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
        Application::syncCompletedApplications();

        $applications = $this->queryFor($request->user())
            ->whereIn('status', ['accepted', 'completed'])
            ->with(['user.agency', 'division.positions', 'position', 'members'])
            ->latest()
            ->get();

        $participants = $applications->flatMap(function (Application $application) {
            $members = $application->members;
            $posisiNama = 'PKL';

            if ($members->isEmpty()) {
                return [[
                    'id' => "application-{$application->id}",
                    'application_id' => $application->id,
                    'nama' => $application->user?->name ?? '-',
                    'nim' => '-',
                    'instansi' => $application->user?->agency?->name ?? '-',
                    'bidang' => $application->division?->nama ?? '-',
                    'posisi' => $posisiNama,
                    'tanggal_mulai' => $application->start_date?->toDateString(),
                    'tanggal_selesai' => $application->end_date?->toDateString(),
                    'tanggal_pengajuan' => $application->created_at?->format('d M Y'),
                    'status' => $application->status,
                ]];
            }

            return $members->map(fn ($member) => [
                'id' => $member->id,
                'application_id' => $application->id,
                'nama' => $member->name,
                'nim' => $member->nim ?? '-',
                'instansi' => $member->school,
                'bidang' => $application->division?->nama ?? '-',
                'posisi' => $posisiNama,
                'tanggal_mulai' => $application->start_date?->toDateString(),
                'tanggal_selesai' => $application->end_date?->toDateString(),
                'tanggal_pengajuan' => $application->created_at?->format('d M Y'),
                'status' => $application->status,
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

    public function completeParticipant(Request $request, Application $application)
    {
        $app = $this->queryFor($request->user())->findOrFail($application->id);

        abort_unless($app->status === 'accepted', 422, 'Hanya peserta dengan status aktif yang dapat diselesaikan.');

        DB::transaction(function () use ($app) {
            $app->update([
                'status' => 'completed',
                'end_date' => $app->end_date && $app->end_date->isPast() ? $app->end_date : now()->toDateString(),
            ]);
        });

        return back()->with('success', 'Peserta PKL berhasil diselesaikan. Kuota bidang telah diperbarui.');
    }

    public function walkInCreate(Request $request)
    {
        Application::syncCompletedApplications();

        $divisions = $this->divisionQuery($request->user())
            ->with('positions:id,division_id,nama')
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
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'after_or_equal:today'],
        ]);

        Application::syncCompletedApplications();

        $division = $this->divisionQuery($request->user())->findOrFail($data['division_id']);
        $occupied = Application::query()
            ->where('division_id', $division->id)
            ->active()
            ->where(function ($query) use ($data) {
                $query->where('start_date', '<=', $data['end_date'])
                    ->where('end_date', '>=', $data['start_date']);
            })
            ->withCount('members')
            ->get()
            ->sum(fn (Application $application) => max(1, $application->members_count));

        if ($occupied >= $division->quota) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'division_id' => 'Kuota bidang penuh untuk periode tanggal tersebut (Slot terisi: ' . $occupied . '/' . $division->quota . ').',
            ]);
        }

        DB::transaction(function () use ($data, $division, $request) {
            $user = User::create([
                'name' => $data['name'],
                'email' => 'walkin+'.Str::uuid().'@pkl.local',
                'password' => Str::random(40),
                'agency_id' => $request->user()->agency_id,
                'tipe_pendaftaran' => 'individu',
            ]);

            $positionId = $data['position_id']
                ?? $division->positions()->orderBy('id')->value('id');

            $application = Application::create([
                'user_id' => $user->id,
                'division_id' => $division->id,
                'position_id' => $positionId,
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