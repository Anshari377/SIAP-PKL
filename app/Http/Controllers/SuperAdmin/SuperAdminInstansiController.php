<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AuditLog;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SuperAdminInstansiController extends Controller
{
    public function index(Request $request)
    {
        $instansiList = Agency::withCount(['divisions as jumlah_bidang_pkl', 'users as jumlah_admin'])
            ->orderBy('name')
            ->get()
            ->map(fn (Agency $agency) => [
                'id' => $agency->id,
                'nama' => $agency->name,
                'tipe' => $agency->type === 'government' ? 'pemerintah' : ($agency->type === 'private' ? 'swasta' : $agency->type),
                'alamat' => $agency->address ?? '-',
                'maps_link' => $agency->maps_link,
                'maps_url' => $agency->maps_url,
                'email' => $agency->contact_email ?? '-',
                'deskripsi' => $agency->description ?? '-',
                'jumlah_bidang_pkl' => $agency->jumlah_bidang_pkl,
                'jumlah_admin' => $agency->jumlah_admin,
            ]);

        return Inertia::render('SuperAdmin/Instansi/Index', [
            'activeNav' => 'superadmin.instansi',
            'instansiList' => $instansiList,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:pemerintah,swasta,government,private'],
            'alamat' => ['nullable', 'string'],
            'maps_link' => ['nullable', 'url', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $typeMap = [
            'pemerintah' => 'government',
            'swasta' => 'private',
            'government' => 'government',
            'private' => 'private',
        ];

        $agency = Agency::create([
            'name' => $data['nama'],
            'type' => $typeMap[$data['tipe']] ?? 'government',
            'address' => $data['alamat'] ?? null,
            'maps_link' => $data['maps_link'] ?? null,
            'contact_email' => $data['email'] ?? null,
            'description' => $data['deskripsi'] ?? null,
        ]);

        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_nama' => $request->user()->name,
                'aksi' => "Tambah Instansi Baru ({$agency->name})",
                'instansi' => $agency->name,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->back()->with('success', "Instansi '{$agency->name}' berhasil ditambahkan.");
    }

    public function update(Request $request, Agency $agency)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'tipe' => ['required', 'string', 'in:pemerintah,swasta,government,private'],
            'alamat' => ['nullable', 'string'],
            'maps_link' => ['nullable', 'url', 'max:500'],
            'email' => ['nullable', 'email', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $typeMap = [
            'pemerintah' => 'government',
            'swasta' => 'private',
            'government' => 'government',
            'private' => 'private',
        ];

        $oldName = $agency->name;

        $agency->update([
            'name' => $data['nama'],
            'type' => $typeMap[$data['tipe']] ?? 'government',
            'address' => $data['alamat'] ?? null,
            'maps_link' => $data['maps_link'] ?? null,
            'contact_email' => $data['email'] ?? null,
            'description' => $data['deskripsi'] ?? null,
        ]);

        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_nama' => $request->user()->name,
                'aksi' => "Ubah Data Instansi ({$agency->name})",
                'instansi' => $agency->name,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->back()->with('success', "Data instansi '{$agency->name}' berhasil diperbarui.");
    }

    public function destroy(Request $request, Agency $agency)
    {
        $agencyName = $agency->name;

        // Unlink related divisions and users safely
        Division::where('agency_id', $agency->id)->update(['agency_id' => null]);
        User::where('agency_id', $agency->id)->update(['agency_id' => null]);

        $agency->delete();

        if ($request->user()) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'user_nama' => $request->user()->name,
                'aksi' => "Hapus Instansi ({$agencyName})",
                'instansi' => $agencyName,
                'ip_address' => $request->ip(),
            ]);
        }

        return redirect()->route('superadmin.instansi.index')->with('success', "Instansi '{$agencyName}' berhasil dihapus.");
    }

    public function show(Agency $agency)
    {
        $agency->load(['divisions.applications' => function ($q) {
            $q->where('status', 'accepted')->withCount('members');
        }, 'users']);

        $bidangPkl = $agency->divisions->map(function (Division $division) {
            $terisi = $division->applications->sum(fn ($app) => max(1, $app->members_count ?? 1));
            $sisa = max(0, $division->quota - $terisi);
            $status = $sisa <= 0 ? 'penuh' : 'aktif';

            return [
                'id' => $division->id,
                'nama' => $division->nama,
                'kuota' => $division->quota,
                'terisi' => $terisi,
                'status' => $status,
            ];
        });

        $adminList = $agency->users->map(function (User $user) {
            return [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'status' => true,
            ];
        });

        return Inertia::render('SuperAdmin/Instansi/Show', [
            'activeNav' => 'superadmin.instansi',
            'instansi' => [
                'id' => $agency->id,
                'nama' => $agency->name,
                'tipe' => $agency->type === 'government' ? 'pemerintah' : ($agency->type === 'private' ? 'swasta' : $agency->type),
                'alamat' => $agency->address ?? '-',
                'maps_link' => $agency->maps_link,
                'maps_url' => $agency->maps_url,
                'email' => $agency->contact_email ?? '-',
                'deskripsi' => $agency->description ?? 'Instansi mitra terdaftar di sistem SIAP-PKL.',
                'created_at' => $agency->created_at ? $agency->created_at->format('d M Y') : '-',
            ],
            'bidangPkl' => $bidangPkl,
            'adminList' => $adminList,
        ]);
    }
}
