<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait InteractsWithDivisions
{
    private function divisions(Request $request): Collection
    {
        $tanggalMulai = $request->input('tanggal_mulai');
        $tanggalSelesai = $request->input('tanggal_selesai');

        return Division::query()
            ->with('positions')
            ->withCount(['applications as accepted_count' => fn ($query) => $this->applyAcceptedQuotaFilter($query, $tanggalMulai, $tanggalSelesai)])
            ->get()
            ->map(fn (Division $division) => $this->withQuota($division));
    }

    private function applyAcceptedQuotaFilter($query, ?string $tanggalMulai = null, ?string $tanggalSelesai = null)
    {
        $query->where('status', 'accepted')->excludeDummy();

        if ($tanggalMulai && $tanggalSelesai) {
            $query->where('start_date', '<=', $tanggalSelesai)
                ->where('end_date', '>=', $tanggalMulai);
        } elseif ($tanggalMulai) {
            $query->where('end_date', '>=', $tanggalMulai);
        } elseif ($tanggalSelesai) {
            $query->where('start_date', '<=', $tanggalSelesai)
                ->where('end_date', '>=', now()->toDateString());
        } else {
            $query->where('end_date', '>=', now()->toDateString());
        }

        return $query;
    }

    private function withQuota(Division $division): Division
    {
        $quota = (int) $division->quota;
        $terisi = (int) ($division->accepted_count ?? 0);
        $sisa = max(0, $quota - $terisi);
        $persentase = $quota > 0 ? (int) round(($terisi / $quota) * 100) : 0;

        $division->setAttribute('kuota_total', $quota);
        $division->setAttribute('terisi_total', $terisi);
        $division->setAttribute('sisa_total', $sisa);
        $division->setAttribute('persentase', $persentase);
        $division->setAttribute('status', $this->statusKey($sisa, $quota));
        $division->setAttribute('jurusan_tags', $division->positions
            ->pluck('jurusan')
            ->flatten()
            ->unique()
            ->take(5)
            ->values());

        return $division;
    }

    private function statusKey(int $sisa, int $quota): string
    {
        if ($sisa <= 0) {
            return 'penuh';
        }

        $sisaPct = $quota > 0 ? ($sisa / $quota) * 100 : 0;

        if ($sisaPct > 50) {
            return 'tersedia';
        }

        if ($sisaPct >= 20) {
            return 'menipis';
        }

        return 'hampir-penuh';
    }

    private function filterDivisions(Collection $divisions, array $filters): Collection
    {
        $search = trim(strtolower((string) ($filters['search'] ?? '')));
        $instansi = trim((string) ($filters['instansi'] ?? ''));
        $status = trim((string) ($filters['status'] ?? ''));

        return $divisions->filter(function (Division $division) use ($search, $instansi, $status) {
            if ($instansi !== '' && $division->instansi !== $instansi) {
                return false;
            }

            if ($status !== '') {
                if ($status === 'penuh') {
                    if (!in_array($division->status, ['penuh', 'hampir-penuh'], true)) {
                        return false;
                    }
                } elseif ($division->status !== $status) {
                    return false;
                }
            }

            if ($search !== '') {
                $posisi = $division->positions->pluck('nama')->implode(' ');
                $haystack = implode(' ', [$division->nama, $division->instansi, $division->kategori, $posisi]);

                if (!str_contains(strtolower($haystack), $search)) {
                    return false;
                }
            }

            return true;
        });
    }
}