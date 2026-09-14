<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminBidangController extends Controller
{
    public function index()
    {
        $divisions = $this->withQuota(
            $this->queryFor(request()->user())
                ->with('positions')
                ->withCount(['applications as accepted_count' => fn ($query) => $query->where('status', 'accepted')])
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
            ]);

            $this->syncPositions($division, $data['posisi'] ?? []);
        });

        return to_route('admin.bidang.index')->with('success', 'Bidang berhasil dibuat.');
    }

    public function show($bidang)
    {
        $division = $this->withQuota(
            $this->queryFor(request()->user())
                ->with('positions')
                ->withCount(['applications as accepted_count' => fn ($query) => $query->where('status', 'accepted')])
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
        $division = $this->queryFor($request->user())->findOrFail($bidang);
        $data = $this->validated($request);

        DB::transaction(function () use ($division, $data) {
            $division->update([
                'nama' => $data['nama'],
                'kategori' => $data['kategori'] ?? $division->kategori,
                'deskripsi' => $data['deskripsi'],
                'quota' => $data['kuota_total'],
            ]);
            $this->syncPositions($division, $data['posisi'] ?? []);
        });

        return to_route('admin.bidang.show', $division)->with('success', 'Bidang berhasil diperbarui.');
    }

    public function destroy(Request $request, $bidang)
    {
        $division = $this->queryFor($request->user())->findOrFail($bidang);
        abort_if($division->applications()->exists(), 422, 'Bidang yang sudah memiliki pengajuan tidak dapat dihapus.');
        $division->delete();

        return to_route('admin.bidang.index')->with('success', 'Bidang berhasil dihapus.');
    }

    private function queryFor($user)
    {
        return $user->agency_id
            ? Division::query()->where('agency_id', $user->agency_id)
            : Division::query()->whereKey(0);
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
        $terisi = (int) ($division->accepted_count ?? 0);

        $division->setAttribute('kuota_total', $quota);
        $division->setAttribute('terisi_total', $terisi);
        $sisa = max(0, $quota - $terisi);
        $sisaPercent = $quota > 0 ? ($sisa / $quota) * 100 : 0;
        $division->setAttribute('status', $sisa <= 0 ? 'penuh' : ($sisaPercent > 50 ? 'tersedia' : ($sisaPercent >= 20 ? 'menipis' : 'hampir-penuh')));

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
            'posisi' => ['nullable', 'array'],
            'posisi.*.id' => ['nullable', 'integer'],
            'posisi.*.nama' => ['required', 'string', 'max:255'],
            'posisi.*.kuota' => ['required', 'integer', 'min:1'],
            'posisi.*.jurusan' => ['nullable', 'string'],
        ]);
    }

    private function syncPositions(Division $division, array $positions): void
    {
        $ids = [];
        foreach ($positions as $position) {
            $record = $position['id'] ?? null
                ? $division->positions()->findOrFail($position['id'])
                : $division->positions()->make();
            $record->fill([
                'nama' => $position['nama'],
                'deskripsi' => $position['nama'],
                'kuota' => $position['kuota'],
                'jurusan' => collect(explode(',', $position['jurusan'] ?? ''))->map(fn ($value) => trim($value))->filter()->values()->all(),
            ]);
            $record->save();
            $ids[] = $record->id;
        }

        if ($ids) {
            $division->positions()->whereNotIn('id', $ids)->where('terisi', 0)->delete();
        }
    }
}