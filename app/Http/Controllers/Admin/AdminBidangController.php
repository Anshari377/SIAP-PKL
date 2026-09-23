<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBidangController extends Controller
{
    public function index()
    {
        Application::syncCompletedApplications();

        $divisions = $this->withQuota(
            $this->queryFor(request()->user())
                ->with([
                    'positions',
                    'applications' => fn ($query) => $query->active()->excludeDummy()->withCount('members'),
                ])
                ->latest()
                ->get(),
        );

        return Inertia::render('Admin/Bidang/Index', [
            'activeNav' => 'admin.bidang',
            'divisions' => $divisions,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Bidang/Create', ['activeNav' => 'admin.bidang']);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $division = $this->queryFor($request->user())->create([
                'agency_id' => $request->user()->agency_id,
                'slug' => Str::slug($data['nama']).'-'.Str::lower(Str::random(6)),
                'nama' => $data['nama'],
                'kategori' => $data['kategori'] ?? 'Umum',
                'instansi' => $request->user()->agency?->name ?? $data['instansi'] ?? '',
                'deskripsi' => $data['deskripsi'],
                'quota' => $data['kuota_total'],
                'jurusan' => $this->parseJurusan($data['jurusan'] ?? ''),
            ]);

            $incomingPositions = $data['positions'] ?? [];
            if (!empty($incomingPositions)) {
                foreach ($incomingPositions as $pos) {
                    $division->positions()->create([
                        'nama' => $pos['nama'],
                        'deskripsi' => $pos['deskripsi'] ?? '',
                        'kuota' => (!empty($pos['kuota']) ? (int) $pos['kuota'] : 1),
                        'jurusan' => is_array($pos['jurusan'] ?? null)
                            ? $pos['jurusan']
                            : $this->parseJurusan($pos['jurusan'] ?? ''),
                    ]);
                }
            } else {
                $division->positions()->create([
                    'nama' => $data['nama'],
                    'deskripsi' => '',
                    'kuota' => $data['kuota_total'] ?: 1,
                    'jurusan' => $this->parseJurusan($data['jurusan'] ?? ''),
                ]);
            }
        });

        return to_route('admin.bidang.index')->with('success', 'Bidang berhasil dibuat.');
    }

    public function show($bidang)
    {
        Application::syncCompletedApplications();

        $division = $this->withQuota(
            $this->queryFor(request()->user())
                ->with([
                    'positions',
                    'applications' => fn ($query) => $query->active()->excludeDummy()->withCount('members'),
                ])
                ->findOrFail($bidang),
        );

        return Inertia::render('Admin/Bidang/Show', ['activeNav' => 'admin.bidang', 'bidang' => $division]);
    }

    public function edit($bidang)
    {
        $division = $this->queryFor(request()->user())->with('positions')->findOrFail($bidang);

        return Inertia::render('Admin/Bidang/Edit', ['activeNav' => 'admin.bidang', 'bidang' => $division]);
    }

    public function update(Request $request, $bidang)
    {
        $division = $this->queryFor($request->user())->with('positions')->findOrFail($bidang);
        $data = $this->validated($request);

        DB::transaction(function () use ($division, $data) {
            $division->update([
                'nama' => $data['nama'],
                'kategori' => $data['kategori'] ?? $division->kategori,
                'deskripsi' => $data['deskripsi'],
                'quota' => $data['kuota_total'],
                'jurusan' => $this->parseJurusan($data['jurusan'] ?? ''),
            ]);

            $incomingPositions = $data['positions'] ?? [];

            if (empty($incomingPositions)) {
                // Jika posisi dikosongkan, pastikan minimal 1 posisi default tetap ada
                if ($division->positions()->count() === 0) {
                    $division->positions()->create([
                        'nama' => $data['nama'],
                        'deskripsi' => '',
                        'kuota' => $data['kuota_total'] ?: 1,
                        'jurusan' => $this->parseJurusan($data['jurusan'] ?? ''),
                    ]);
                }
            } else {
                $incomingIds = collect($incomingPositions)->pluck('id')->filter()->values();

                // Hapus posisi yang tidak ada di list baru (kecuali yang masih punya pengajuan aktif)
                $division->positions()
                    ->whereNotIn('id', $incomingIds)
                    ->whereDoesntHave('applications', fn ($q) => $q->whereIn('status', ['pending', 'accepted', 'revision']))
                    ->delete();

                foreach ($incomingPositions as $pos) {
                    if (!empty($pos['id'])) {
                        // Update posisi existing menggunakan Eloquent model
                        $position = $division->positions()->find($pos['id']);
                        if ($position) {
                            $updateData = ['nama' => $pos['nama']];
                            if (isset($pos['deskripsi'])) {
                                $updateData['deskripsi'] = $pos['deskripsi'] ?? '';
                            }
                            if (isset($pos['kuota']) && $pos['kuota'] !== '' && $pos['kuota'] !== null) {
                                $updateData['kuota'] = (int) $pos['kuota'];
                            }
                            if (isset($pos['jurusan'])) {
                                $updateData['jurusan'] = is_array($pos['jurusan'])
                                    ? $pos['jurusan']
                                    : $this->parseJurusan($pos['jurusan'] ?? '');
                            }
                            $position->update($updateData);
                        }
                    } else {
                        // Buat posisi baru
                        $division->positions()->create([
                            'nama' => $pos['nama'],
                            'deskripsi' => $pos['deskripsi'] ?? '',
                            'kuota' => (!empty($pos['kuota']) ? (int) $pos['kuota'] : 1),
                            'jurusan' => is_array($pos['jurusan'] ?? null)
                                ? $pos['jurusan']
                                : $this->parseJurusan($pos['jurusan'] ?? ''),
                        ]);
                    }
                }
            }
        });

        return to_route('admin.bidang.show', $division)->with('success', 'Bidang berhasil diperbarui.');
    }

    public function destroy(Request $request, $bidang)
    {
        $division = $this->queryFor($request->user())->findOrFail($bidang);
        abort_if($division->applications()->excludeDummy()->exists(), 422, 'Bidang yang sudah memiliki pengajuan tidak dapat dihapus.');
        $division->delete();

        return to_route('admin.bidang.index')->with('success', 'Bidang berhasil dihapus.');
    }

    private function queryFor($user)
    {
        if ($user->hasRole('super_admin')) {
            return Division::query();
        }

        return $user->agency_id
            ? Division::query()->where('agency_id', $user->agency_id)
            : Division::query()->whereNull('agency_id');
    }

    private function withQuota($divisions)
    {
        return $divisions instanceof Division
            ? $this->setQuotaAttributes($divisions)
            : $divisions->map(fn (Division $division) => $this->setQuotaAttributes($division));
    }

    private function setQuotaAttributes(Division $division): Division
    {
        $quota = (int) $division->quota;
        $terisi = $division->relationLoaded('applications')
            ? $division->applications->sum(fn (Application $app) => max(1, $app->members_count ?? 1))
            : (int) ($division->accepted_count ?? 0);

        // Kuota terisi aktif tidak boleh melebihi kuota untuk tampilan kuota
        $terisiTotal = min($quota, $terisi);
        $sisa = max(0, $quota - $terisiTotal);
        $sisaPercent = $quota > 0 ? ($sisa / $quota) * 100 : 0;
        $persentase = $quota > 0 ? (int) round(($terisiTotal / $quota) * 100) : 0;

        $division->setAttribute('kuota_total', $quota);
        $division->setAttribute('terisi_total', $terisiTotal);
        $division->setAttribute('sisa_total', $sisa);
        $division->setAttribute('persentase', $persentase);
        $division->setAttribute('status', $sisa <= 0 ? 'penuh' : ($sisaPercent > 50 ? 'tersedia' : ($sisaPercent >= 20 ? 'menipis' : 'hampir-penuh')));
        $division->setAttribute('jurusan_tags', $division->relationLoaded('positions')
            ? $division->positions->pluck('jurusan')->flatten()->unique()->take(5)->values()
            : collect());

        return $division;
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['nullable', 'string', 'max:255'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['required', 'string'],
            'kuota_total' => ['required', 'integer', 'min:1'],
            'jurusan' => ['nullable', 'string'],
            'positions' => ['nullable', 'array'],
            'positions.*.id' => ['nullable', 'integer'],
            'positions.*.nama' => ['required_with:positions', 'string', 'max:255'],
            'positions.*.deskripsi' => ['nullable', 'string'],
            'positions.*.kuota' => ['nullable', 'integer', 'min:1'],
            'positions.*.jurusan' => ['nullable', 'string'],
        ]);
    }

    private function parseJurusan(?string $jurusan): array
    {
        return collect(explode(',', (string) $jurusan))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->values()
            ->all();
    }
}