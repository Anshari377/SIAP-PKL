<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PengajuanPklController extends Controller
{
    public function index(Request $request)
    {
        $divisions = Division::query()
            ->orderBy('nama')
            ->get()
            ->map(function (Division $division) {
                $occupied = Application::where('division_id', $division->id)
                    ->where('status', 'accepted')
                    ->withCount('members')
                    ->get()
                    ->sum(fn (Application $app) => 1 + $app->members_count);

                return [
                    'id' => $division->id,
                    'nama' => $division->nama,
                    'instansi' => $division->instansi,
                    'kuota' => $division->quota,
                    'kuota_sisa' => max(0, $division->quota - $occupied),
                ];
            });

        return Inertia::render('Pengajuan/Index', [
            'activeNav' => 'pengajuan',
            'divisions' => $divisions,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
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
        ]);

        $divisionId = $data['division_id'];
        $startDate = $data['start_date'];
        $endDate = $data['end_date'];
        $requestedSize = 1 + count($data['members'] ?? []);

        $request->user()->update([
            'tipe_pendaftaran' => $data['tipe'],
        ]);

        $overlappingApps = Application::where('division_id', $divisionId)
            ->where('status', 'accepted')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->withCount('members')
            ->get();

        $currentOccupied = $overlappingApps->sum(function (Application $app) {
            return 1 + $app->members_count;
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

    public function checkAvailability(Request $request)
    {
        $request->validate([
            'division_id' => ['required', 'integer', 'exists:divisions,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $divisionId = $request->input('division_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $overlappingApps = Application::where('division_id', $divisionId)
            ->where('status', 'accepted')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->withCount('members')
            ->get();

        $currentOccupied = $overlappingApps->sum(function (Application $app) {
            return 1 + $app->members_count;
        });

        $division = Division::findOrFail($divisionId);
        $availableSlots = $division->quota - $currentOccupied;

        if ($availableSlots >= 1) {
            return response()->json(['available' => true]);
        }

        $earliestEndDate = Application::where('division_id', $divisionId)
            ->where('status', 'accepted')
            ->where('end_date', '>=', $startDate)
            ->min('end_date');

        $nextAvailableDate = $earliestEndDate
            ? \Carbon\Carbon::parse($earliestEndDate)->addDay()->format('Y-m-d')
            : $startDate;

        return response()->json([
            'available' => false,
            'message' => 'Kuota penuh untuk periode ini',
            'next_available_date' => $nextAvailableDate,
        ]);
    }
}