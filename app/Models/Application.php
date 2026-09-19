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
}