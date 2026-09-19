<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PengajuanPklController extends Controller
{
    public function index(Request $request)
    {
        Application::syncCompletedApplications();

        $activeApplication = Application::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'accepted', 'revision'])
            ->with('division')
            ->latest()
            ->first();

        $divisions = Division::query()
            ->with('positions:id,division_id,nama')
            ->orderBy('nama')
            ->get()
            ->map(function (Division $division) {
                $occupied = Application::where('division_id', $division->id)
                    ->active()
                    ->excludeDummy()
                    ->withCount('members')
                    ->get()
                    ->sum(fn (Application $app) => max(1, $app->members_count));

                $occupied = min($division->quota, $occupied);

                return [
                    'id' => $division->id,
                    'nama' => $division->nama,
                    'instansi' => $division->instansi,
                    'kuota' => $division->quota,
                    'kuota_terisi' => $occupied,
                    'kuota_sisa' => max(0, $division->quota - $occupied),
                    'positions' => $division->positions->map(fn ($p) => [
                        'id' => $p->id,
                        'nama' => $p->nama,
                    ])->values(),
                ];
            });

        return Inertia::render('Pengajuan/Index', [
            'activeNav' => 'pengajuan',
            'divisions' => $divisions,
            'hasActiveApplication' => $activeApplication !== null,
            'activeApplication' => $activeApplication ? [
                'id' => $activeApplication->id,
                'status' => $activeApplication->status,
                'division_nama' => $activeApplication->division?->nama ?? '-',
                'instansi' => $activeApplication->division?->instansi ?? '-',
                'created_at' => $activeApplication->created_at?->format('d M Y'),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $existingActive = Application::where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'accepted', 'revision'])
            ->first();

        if ($existingActive) {
            return back()->withErrors([
                'message' => 'Anda sudah memiliki permohonan aktif, silakan selesaikan atau tunggu prosesnya sebelum mengajukan permohonan baru.',
            ]);
        }

        $data = $request->validate([
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'after_or_equal:today'],
            'tipe' => ['required', 'in:individu,kelompok'],
            'ketua.name' => ['required', 'string', 'max:255'],
            'ketua.nim' => ['nullable', 'string', 'max:50'],
            'ketua.school' => ['required', 'string', 'max:255'],
            'ketua.major' => ['required', 'string', 'max:255'],
            'ketua.phone' => ['required', 'string', 'max:30'],
            'members' => ['required_if:tipe,kelompok', 'array', 'min:1'],
            'members.*.name' => ['required', 'string', 'max:255'],
            'members.*.nim' => ['nullable', 'string', 'max:50'],
            'members.*.school' => ['required', 'string', 'max:255'],
            'members.*.major' => ['required', 'string', 'max:255'],
            'members.*.phone' => ['required', 'string', 'max:30'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
            'consent_pdp' => ['required', 'accepted'],
        ]);

        $divisionId = $data['division_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $requestedSize = 1 + count($data['members'] ?? []);

        $request->user()->update([
            'tipe_pendaftaran' => $data['tipe'],
        ]);

        $overlappingApps = Application::where('division_id', $divisionId)
            ->active()
            ->excludeDummy()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->withCount('members')
            ->get();

        $currentOccupied = $overlappingApps->sum(function (Application $app) {
            return max(1, $app->members_count);
        });

        $division = Division::findOrFail($divisionId);
        $availableSlots = $division->quota - $currentOccupied;

        if ($requestedSize > $availableSlots) {
            return response()->json(['message' => 'Kuota tidak mencukupi untuk periode tersebut'], 422);
        }

        $file = $request->file('document');
        $nama = sprintf(
            'surat_pengajuan_user%d_%s_%s.pdf',
            $request->user()->id,
            now()->format('YmdHis'),
            Str::random(6)
        );
        $documentPath = $file->storeAs('applications', $nama, 'public');

        $application = Application::create([
            'user_id' => $request->user()->id,
            'division_id' => $divisionId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'pending',
            'document_path' => $documentPath,
            'consent_pdp' => true,
        ]);

        $application->members()->create([
            'name' => $data['ketua']['name'],
            'nim' => $data['ketua']['nim'] ?? null,
            'school' => $data['ketua']['school'],
            'major' => $data['ketua']['major'],
            'phone' => $data['ketua']['phone'],
        ]);

        foreach ($data['members'] ?? [] as $member) {
            $application->members()->create([
                'name' => $member['name'],
                'nim' => $member['nim'] ?? null,
                'school' => $member['school'],
                'major' => $member['major'],
                'phone' => $member['phone'],
            ]);
        }

        return redirect()->route('pengajuan.index')->with('success', 'Pengajuan PKL berhasil dikirim dan menunggu verifikasi.');
    }

    public function reupload(Request $request, Application $application)
    {
        abort_unless($application->user_id === $request->user()->id && $application->status === 'revision', 403);

        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        if ($application->document_path && Storage::disk('public')->exists($application->document_path)) {
            Storage::disk('public')->delete($application->document_path);
        }

        $file = $request->file('document');
        $nama = sprintf(
            'surat_pengajuan_user%d_%s_%s.pdf',
            $request->user()->id,
            now()->format('YmdHis'),
            Str::random(6)
        );
        $documentPath = $file->storeAs('applications', $nama, 'public');

        $application->update([
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return redirect()->route('riwayat.index')->with('success', 'Berkas revisi berhasil diunggah ulang dan status pengajuan kembali dalam proses verifikasi.');
    }

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date', 'after_or_equal:today'],
        ]);

        Application::syncCompletedApplications();

        $divisionId = $request->input('division_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $division = Division::findOrFail($divisionId);

        // Hitung berapa slot yang terisi di periode yang diminta (overlap)
        $overlappingApps = Application::where('division_id', $divisionId)
            ->active()
            ->excludeDummy()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->withCount('members')
            ->get();

        $currentOccupied = $overlappingApps->sum(fn (Application $app) => max(1, $app->members_count));
        $availableSlots = $division->quota - $currentOccupied;

        if ($availableSlots >= 1) {
            return response()->json([
                'available' => true,
                'slot_tersedia' => $availableSlots,
                'slot_terisi_periode' => $currentOccupied,
                'kuota_total' => $division->quota,
            ]);
        }

        $earliestEndDate = Application::where('division_id', $divisionId)
            ->active()
            ->excludeDummy()
            ->where('end_date', '>=', $startDate)
            ->min('end_date');

        $nextAvailableDate = $earliestEndDate
            ? \Carbon\Carbon::parse($earliestEndDate)->addDay()->format('Y-m-d')
            : $startDate;

        return response()->json([
            'available' => false,
            'message' => 'Kuota penuh untuk periode ini',
            'next_available_date' => $nextAvailableDate,
            'slot_tersedia' => 0,
            'slot_terisi_periode' => $currentOccupied,
            'kuota_total' => $division->quota,
        ]);
    }
}