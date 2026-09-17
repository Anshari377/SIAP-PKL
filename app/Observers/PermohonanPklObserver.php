<?php

namespace App\Observers;

use App\Models\PermohonanPkl;
use Spatie\Activitylog\Models\Activity;

class PermohonanPklObserver
{
    public function updated(PermohonanPkl $permohonan): void
    {
        if ($permohonan->isDirty('status')) {
            $newStatus = $permohonan->status;
            $action = match ($newStatus) {
                'diterima' => 'Terima Pengajuan',
                'ditolak' => 'Tolak Pengajuan',
                'revisi' => 'Revisi Pengajuan',
                'completed', 'selesai' => 'Selesaikan PKL',
                default => 'Ubah Status Pengajuan',
            };

            $instansiId = $permohonan->subInstansi?->id_instansi;

            activity()
                ->causedBy(auth()->user())
                ->performedOn($permohonan)
                ->withProperties([
                    'ip' => request()->ip(),
                    'instansi_id' => $instansiId,
                    'old_status' => $permohonan->getOriginal('status'),
                    'new_status' => $newStatus,
                ])
                ->log($action);
        }
    }

    public function created(PermohonanPkl $permohonan): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($permohonan)
            ->withProperties([
                'ip' => request()->ip(),
                'instansi_id' => $permohonan->subInstansi?->id_instansi,
            ])
            ->log('Submit Pengajuan');
    }
}
