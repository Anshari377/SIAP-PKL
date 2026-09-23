<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Application;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'division_id', 'nama', 'deskripsi', 'kuota', 'terisi', 'kualifikasi', 'jurusan',
    ];

    protected $casts = [
        'kualifikasi' => 'array',
        'jurusan' => 'array',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function pengajuan(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
