<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgencyInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id', 'email', 'is_redeemed',
    ];

    protected $casts = [
        'is_redeemed' => 'boolean',
    ];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
