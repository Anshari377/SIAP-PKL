<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Position;

class Division extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id', 'slug', 'nama', 'kategori', 'instansi', 'deskripsi', 'quota',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
