<?php

namespace App\Observers;

use App\Models\SubInstansi;

class SubInstansiObserver
{
    public function created(SubInstansi $subInstansi): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($subInstansi)
            ->withProperties([
                'ip' => request()->ip(),
                'instansi_id' => $subInstansi->id_instansi,
                'nama_bidang' => $subInstansi->nama_sub_instansi,
            ])
            ->log('Tambah Bidang');
    }

    public function updated(SubInstansi $subInstansi): void
    {
        $action = $subInstansi->isDirty('batas_kuota') ? 'Ubah Kuota' : 'Edit Bidang';

        activity()
            ->causedBy(auth()->user())
            ->performedOn($subInstansi)
            ->withProperties([
                'ip' => request()->ip(),
                'instansi_id' => $subInstansi->id_instansi,
                'changes' => $subInstansi->getChanges(),
            ])
            ->log($action);
    }

    public function deleted(SubInstansi $subInstansi): void
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($subInstansi)
            ->withProperties([
                'ip' => request()->ip(),
                'instansi_id' => $subInstansi->id_instansi,
            ])
            ->log('Hapus Bidang');
    }
}
