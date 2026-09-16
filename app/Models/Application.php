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
        'start_date',
        'end_date',
        'status',
        'is_walk_in',
        'document_path',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ApplicationMember::class);
    }

    public function scopeExcludeDummy($query)
    {
        return $query->whereHas('user', fn ($q) => $q->where('email', 'not like', 'demo.%@pkl.test'));
    }
}