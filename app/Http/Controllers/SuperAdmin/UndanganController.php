<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UndanganController extends Controller
{
    public function index()
    {
        $instansiList = Instansi::orderBy('nama_instansi')->get(['id', 'nama_instansi']);

        $adminUsers = User::role('agency_admin')
            ->with('instansi')
            ->latest()
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'email' => $user->email,
                'nama_lengkap' => $user->nama_lengkap,
                'instansi' => $user->instansi?->nama_instansi ?? 'Belum Ditentukan',
                'id_instansi' => $user->id_instansi,
                'google_id' => $user->google_id,
                'status_google' => $user->google_id ? 'connected' : 'draft',
                'created_at' => $user->created_at?->format('Y-m-d H:i:s') ?? '-',
            ]);

        return Inertia::render('SuperAdmin/Undangan/Index', [
            'activeNav' => 'superadmin.undangan',
            'instansiList' => $instansiList,
            'adminUsers' => $adminUsers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'id_instansi' => ['required', 'integer', 'exists:instansi,id'],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
        ]);

        $instansi = Instansi::findOrFail($data['id_instansi']);
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            // Bind existing user to agency_admin role and target instansi
            $user->update([
                'id_instansi' => $instansi->id,
            ]);
            $user->syncRoles(['agency_admin']);
            $action = "Assign Admin Exists ({$user->email}) ke Instansi {$instansi->nama_instansi}";
        } else {
            // Direct provisioning: insert new admin user record
            $name = $data['nama_lengkap'] ?: strstr($data['email'], '@', true);
            $name = ucwords(str_replace(['.', '_', '-'], ' ', $name));

            $user = User::create([
                'email' => $data['email'],
                'nama_lengkap' => $name,
                'id_instansi' => $instansi->id,
            ]);
            $user->syncRoles(['agency_admin']);
            $action = "Tambah Admin Baru ({$user->email}) ke Instansi {$instansi->nama_instansi}";
        }

        activity()
            ->causedBy($request->user())
            ->performedOn($user)
            ->withProperties([
                'ip' => $request->ip(),
                'instansi_id' => $instansi->id,
                'email' => $user->email,
            ])
            ->log('Undang Admin');

        return redirect()->back()->with('success', "Admin {$user->email} berhasil ditambahkan/dihubungkan ke {$instansi->nama_instansi}.");
    }

    public function destroy(User $user)
    {
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'ip' => request()->ip(),
                'email' => $user->email,
            ])
            ->log('Hapus Admin');

        $user->removeRole('agency_admin');
        $user->update(['id_instansi' => null]);

        return redirect()->back()->with('success', "Akses admin untuk {$user->email} telah dicabut.");
    }
}
