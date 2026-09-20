<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UndanganController extends Controller
{
    public function index()
    {
        $instansiList = Agency::orderBy('name')->get(['id', 'name']);

        $adminUsers = User::role('agency_admin')
            ->with('agency')
            ->latest()
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'email' => $user->email,
                'nama' => $user->name,
                'instansi' => $user->agency?->name ?? 'Belum Ditentukan',
                'agency_id' => $user->agency_id,
                'status' => $user->google_id ? 'claimed' : 'pending',
                'tanggal' => $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-',
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
            'agency_id' => ['required', 'exists:agencies,id'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $agency = Agency::findOrFail($data['agency_id']);
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            $user->update([
                'agency_id' => $agency->id,
            ]);
            if (! $user->hasRole('agency_admin')) {
                $user->assignRole('agency_admin');
            }
        } else {
            $name = $data['name'] ?? ucwords(str_replace(['.', '_', '-'], ' ', strstr($data['email'], '@', true)));
            $user = User::create([
                'name' => $name,
                'email' => $data['email'],
                'agency_id' => $agency->id,
                'password' => bcrypt(Str::random(16)),
            ]);
            $user->assignRole('agency_admin');
        }

        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_nama' => $request->user()->name,
                'aksi' => "Undang Admin ({$user->email})",
                'instansi' => $agency->name,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->back()->with('success', "Undangan berhasil dikirim ke {$user->email}.");
    }

    public function destroy(User $user, Request $request)
    {
        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_nama' => $request->user()->name,
                'aksi' => "Batalkan Undangan Admin ({$user->email})",
                'instansi' => $user->agency?->name ?? '-',
                'ip_address' => $request->ip(),
            ]);
        }

        $user->removeRole('agency_admin');
        $user->update(['agency_id' => null]);

        return redirect()->back()->with('success', "Akses/undangan admin untuk {$user->email} telah dicabut.");
    }
}
