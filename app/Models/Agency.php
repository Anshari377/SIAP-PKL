<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'nama_singkat', 'slug', 'logo', 'type', 'address', 'maps_link', 'contact_email', 'description',
    ];

    protected $appends = ['maps_url'];

    public function getMapsUrlAttribute(): ?string
    {
        if ($this->maps_link) {
            return $this->maps_link;
        }

        if ($this->address) {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->name . ' ' . $this->address);
        }

        return null;
    }

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(AgencyInvitation::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
