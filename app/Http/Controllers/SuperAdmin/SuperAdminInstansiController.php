<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuperAdminInstansiController extends Controller
{
    public function index(Request $request)
    {
        $instansiList = Instansi::withCount(['subInstansi as jumlah_bidang_pkl', 'users as jumlah_admin'])
            ->orderBy('nama_instansi')
            ->get()
            ->map(fn (Instansi $ins) => [
                'id' => $ins->id,
                'nama' => $ins->nama_instansi,
                'nama_instansi' => $ins->nama_instansi,
                'deskripsi_singkat' => $ins->deskripsi_singkat,
                'alamat' => $ins->alamat ?? 'Alamat belum diisi',
                'status' => $ins->status ?? 'aktif',
                'jumlah_bidang_pkl' => $ins->jumlah_bidang_pkl,
                'jumlah_admin' => $ins->jumlah_admin,
            ]);

        return Inertia::render('SuperAdmin/Instansi/Index', [
            'activeNav' => 'superadmin.instansi',
            'instansiList' => $instansiList,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_instansi' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'deskripsi_singkat' => ['nullable', 'string'],
        ]);

        $instansi = Instansi::create([
            'nama_instansi' => $data['nama_instansi'],
            'alamat' => $data['alamat'] ?? null,
            'deskripsi_singkat' => $data['deskripsi_singkat'] ?? null,
            'status' => 'aktif',
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($instansi)
            ->withProperties([
                'ip' => $request->ip(),
                'instansi_id' => $instansi->id,
            ])
            ->log('Tambah Instansi');

        return redirect()->back()->with('success', "Instansi '{$instansi->nama_instansi}' berhasil ditambahkan.");
    }

    public function show(Instansi $instansi)
    {
        $instansi->load(['subInstansi', 'users']);

        $admins = $instansi->users->map(fn (User $user) => [
            'id' => $user->id,
            'nama_lengkap' => $user->nama_lengkap,
            'email' => $user->email,
            'google_id' => $user->google_id,
            'status_google' => $user->google_id ? 'connected' : 'draft',
        ]);

        return Inertia::render('SuperAdmin/Instansi/Show', [
            'activeNav' => 'superadmin.instansi',
            'instansi' => [
                'id' => $instansi->id,
                'nama_instansi' => $instansi->nama_instansi,
                'alamat' => $instansi->alamat,
                'deskripsi_singkat' => $instansi->deskripsi_singkat,
                'sub_instansi' => $instansi->subInstansi,
                'admins' => $admins,
            ],
        ]);
    }
}
