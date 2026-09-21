<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $table = 'applications';

    protected $fillable = [
        'user_id',
        'division_id',
        'position_id',
        'start_date',
        'end_date',
        'status',
        'is_walk_in',
        'document_path',
        'surat_balasan_path',
        'consent_pdp',
        'catatan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'consent_pdp' => 'boolean',
            'is_walk_in' => 'boolean',
        ];
    }

    protected $appends = ['surat_balasan_url'];

    public function getSuratBalasanUrlAttribute(): ?string
    {
        if (! $this->surat_balasan_path) {
            return null;
        }

        try {
            return route('pengajuan.surat-balasan', $this->id);
        } catch (\Throwable $e) {
            return '/storage/'.ltrim($this->surat_balasan_path, '/');
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ApplicationMember::class);
    }

    public function scopeExcludeDummy($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('email', 'not like', 'demo.%@pkl.test'));
    }

    public function scopeActive($query)
    {
        return $query
            ->where('status', 'accepted')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', now()->toDateString());
    }

    public function scopeCurrentlyActive($query)
    {
        return $query
            ->where('status', 'accepted')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString());
    }

    public static function syncCompletedApplications(): void
    {
        // Selesaikan aplikasi yang tanggal berakhirnya sudah lewat hari ini.
        // Logika sederhana: jika end_date < today maka status = completed.
        // Tampilan 'selesai' di hari yang sama (end_date = today) ditangani
        // di sisi frontend (isPastEndDate) dan tombol 'Selesaikan' manual.
        static::query()
            ->where('status', 'accepted')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->each(function (self $app) {
                $app->update(['status' => 'completed']);
            });
    }

    /**
     * Menghitung ketersediaan kuota bidang pada rentang periode tertentu.
     * Menggunakan kalkulasi beban harian konkuren puncak (peak occupancy),
     * bukan sekadar menjumlahkan seluruh aplikasi lintas periode.
     */
    public static function checkPeriodAvailability(
        int $divisionId,
        string $startDate,
        string $endDate,
        int $requiredSlots = 1,
        ?int $excludeApplicationId = null
    ): array {
        static::syncCompletedApplications();

        $division = Division::findOrFail($divisionId);
        $quota = (int) $division->quota;

        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        // Ambil semua aplikasi aktif yang beririsan dengan [start, end]
        $overlappingApps = static::where('division_id', $divisionId)
            ->when($excludeApplicationId, fn ($q) => $q->where('id', '!=', $excludeApplicationId))
            ->active()
            ->where(function ($query) use ($start, $end) {
                $query->whereDate('start_date', '<=', $end->toDateString())
                    ->whereDate('end_date', '>=', $start->toDateString());
            })
            ->withCount('members')
            ->get();

        // Hitung peak occupancy (beban slot harian tertinggi dalam rentang periode)
        $peakOccupied = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            $curDate = $current->toDateString();
            $dailyOccupied = 0;
            foreach ($overlappingApps as $app) {
                $appStart = $app->start_date ? \Carbon\Carbon::parse($app->start_date)->toDateString() : null;
                $appEnd = $app->end_date ? \Carbon\Carbon::parse($app->end_date)->toDateString() : null;
                if ($appStart && $appEnd && $appStart <= $curDate && $appEnd >= $curDate) {
                    $dailyOccupied += max(1, (int) $app->members_count);
                }
            }
            if ($dailyOccupied > $peakOccupied) {
                $peakOccupied = $dailyOccupied;
            }
            $current->addDay();
        }

        $availableSlots = max(0, $quota - $peakOccupied);
        $isAvailable = ($availableSlots >= $requiredSlots);

        $nextAvailableDate = null;
        if (! $isAvailable) {
            $durationDays = $start->diffInDays($end) + 1;
            $nextAvailableDate = static::findNextAvailableStartDate(
                $divisionId,
                $startDate,
                $durationDays,
                $requiredSlots,
                $excludeApplicationId,
                $quota
            );
        }

        return [
            'available' => $isAvailable,
            'slot_tersedia' => $availableSlots,
            'slot_terisi_periode' => min($quota, $peakOccupied),
            'kuota_total' => $quota,
            'next_available_date' => $nextAvailableDate,
        ];
    }

    /**
     * Mencari tanggal mulai berikutnya yang BENAR-BENAR tersedia
     * untuk durasi dan slot yang diminta.
     */
    public static function findNextAvailableStartDate(
        int $divisionId,
        string $startDate,
        int $durationDays = 1,
        int $requiredSlots = 1,
        ?int $excludeApplicationId = null,
        ?int $quota = null
    ): ?string {
        if ($quota === null) {
            $division = Division::findOrFail($divisionId);
            $quota = (int) $division->quota;
        }

        $futureApps = static::where('division_id', $divisionId)
            ->when($excludeApplicationId, fn ($q) => $q->where('id', '!=', $excludeApplicationId))
            ->active()
            ->whereDate('end_date', '>=', $startDate)
            ->withCount('members')
            ->get();

        if ($futureApps->isEmpty()) {
            return $startDate;
        }

        // Kumpulkan semua titik tanggal potensi pergantian slot (hari setelah aplikasi selesai)
        $candidateDates = [];
        foreach ($futureApps as $app) {
            if ($app->end_date) {
                $candidate = \Carbon\Carbon::parse($app->end_date)->addDay()->toDateString();
                if ($candidate >= $startDate && ! in_array($candidate, $candidateDates, true)) {
                    $candidateDates[] = $candidate;
                }
            }
        }

        sort($candidateDates);

        // Uji setiap kandidat tanggal secara berurutan
        foreach ($candidateDates as $candDate) {
            $candStart = \Carbon\Carbon::parse($candDate);
            $candEnd = $candStart->copy()->addDays(max(0, $durationDays - 1));

            $candEndStr = $candEnd->toDateString();
            $candStartStr = $candStart->toDateString();

            $candOverlapping = $futureApps->filter(function ($app) use ($candStartStr, $candEndStr) {
                $appStart = $app->start_date ? \Carbon\Carbon::parse($app->start_date)->toDateString() : null;
                $appEnd = $app->end_date ? \Carbon\Carbon::parse($app->end_date)->toDateString() : null;
                return $appStart && $appEnd && $appStart <= $candEndStr && $appEnd >= $candStartStr;
            });

            $peak = 0;
            $curr = $candStart->copy();
            while ($curr->lte($candEnd)) {
                $cStr = $curr->toDateString();
                $occ = 0;
                foreach ($candOverlapping as $app) {
                    $appStart = \Carbon\Carbon::parse($app->start_date)->toDateString();
                    $appEnd = \Carbon\Carbon::parse($app->end_date)->toDateString();
                    if ($appStart <= $cStr && $appEnd >= $cStr) {
                        $occ += max(1, (int) $app->members_count);
                    }
                }
                if ($occ > $peak) {
                    $peak = $occ;
                }
                $curr->addDay();
            }

            if (($quota - $peak) >= $requiredSlots) {
                return $candDate;
            }
        }

        // Jika semua kandidat periode penuh, tanggal setelah aplikasi terjauh berakhir pasti kosong
        $latestEndDate = $futureApps->max('end_date');
        if ($latestEndDate) {
            return \Carbon\Carbon::parse($latestEndDate)->addDay()->toDateString();
        }

        return $startDate;
    }
}