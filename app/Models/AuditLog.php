<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'user_nama', 'aksi', 'instansi', 'ip_address', 'perubahan'];

    protected $casts = [
        'perubahan' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}