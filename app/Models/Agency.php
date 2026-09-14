<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'address', 'contact_email',
    ];

    public function invitations(): HasMany
    {
        return $this->hasMany(AgencyInvitation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
