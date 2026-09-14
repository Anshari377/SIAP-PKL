<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const instansiFilter = ref('');
const aksiFilter = ref('');
const tanggalMulai = ref('');
const tanggalSelesai = ref('');

const logList = ref([
    { id: 1, waktu: '2026-09-08 09:12:00', user: 'Budi Santoso, S.Kom', aksi: 'Terima Pengajuan', instansi: 'Diskominfo Kaltim', ip: '36.85.19.42', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'accepted' },
        { field: 'Catatan Respon', old: '-', new: 'Diterima, silakan mengikuti briefing.' },
    ] },
    { id: 2, waktu: '2026-09-08 08:47:00', user: 'Siti Aminah, S.Kom', aksi: 'Tambah Walk-in', instansi: 'RSUD Abdul Wahab Sjahranie', ip: '36.85.21.107', perubahan: [
        { field: 'Nama Peserta', old: '-', new: 'Damar Wicaksono' },
        { field: 'Bidang PKL', old: '-', new: 'Bidang Pelayanan Medis' },
        { field: 'Kuota Terpakai', old: '7', new: '8' },
    ] },
    { id: 3, waktu: '2026-09-07 15:30:00', user: 'Budi Santoso, S.Kom', aksi: 'Ubah Kuota', instansi: 'Diskominfo Kaltim', ip: '36.85.19.42', perubahan: [
        { field: 'Kuota Bidang Aplikasi E-Gov', old: '8', new: '6' },
        { field: 'Kuota Bidang Jaringan', old: '6', new: '8' },
    ] },
    { id: 4, waktu: '2026-09-07 11:05:00', user: 'Andi Wijaya', aksi: 'Tolak Pengajuan', instansi: 'Dinas Pendidikan Kaltim', ip: '114.122.200.15', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'rejected' },
        { field: 'Alasan', old: '-', new: 'Berkas tidak lengkap.' },
    ] },
    { id: 5, waktu: '2026-09-07 10:22:00', user: 'Rina Marlina', aksi: 'Ubah Status Bidang', instansi: 'BANKALTIMTARA', ip: '36.85.33.81', perubahan: [
        { field: 'Status Bidang', old: 'nonaktif', new: 'aktif' },
    ] },
    { id: 6, waktu: '2026-09-06 16:44:00', user: 'Admin Pusat', aksi: 'Tambah Instansi', instansi: 'PT Telkom Indonesia Witel Samarinda', ip: '103.20.75.11', perubahan: [
        { field: 'Nama Instansi', old: '-', new: 'PT Telkom Indonesia Witel Samarinda' },
        { field: 'Tipe', old: '-', new: 'swasta' },
    ] },
    { id: 7, waktu: '2026-09-06 14:18:00', user: 'Nur Aida', aksi: 'Tolak Pengajuan', instansi: 'RSUD Abdul Wahab Sjahranie', ip: '36.85.21.107', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'rejected' },
    ] },
    { id: 8, waktu: '2026-09-06 09:57:00', user: 'Yoga Pratama', aksi: 'Ubah Kuota', instansi: 'BANKALTIMTARA', ip: '36.85.33.81', perubahan: [
        { field: 'Kuota Posisi Teller', old: '4', new: '3' },
    ] },
    { id: 9, waktu: '2026-09-05 13:30:00', user: 'Budi Santoso, S.Kom', aksi: 'Tambah Walk-in', instansi: 'Diskominfo Kaltim', ip: '36.85.19.42', perubahan: [
        { field: 'Nama Peserta', old: '-', new: 'Reza Pratama' },
        { field: 'Kuota Terpakai', old: '4', new: '5' },
    ] },
    { id: 10, waktu: '2026-09-05 10:04:00', user: 'Lina Marlina', aksi: 'Ubah Status Bidang', instansi: 'Dinas Pendidikan Kaltim', ip: '114.122.200.15', perubahan: [
        { field: 'Status Bidang', old: 'aktif', new: 'nonaktif' },
    ] },
    { id: 11, waktu: '2026-09-04 15:41:00', user: 'Admin Pusat', aksi: 'Undang Admin', instansi: 'Diskominfo Kaltim', ip: '103.20.75.11', perubahan: [
        { field: 'Email Calon Admin', old: '-', new: 'rudi.hartono@diskominfo.kaltim.go.id' },
        { field: 'Status Undangan', old: '-', new: 'pending' },
    ] },
    { id: 12, waktu: '2026-09-04 11:26:00', user: 'Nur Aida', aksi: 'Terima Pengajuan', instansi: 'RSUD Abdul Wahab Sjahranie', ip: '36.85.21.107', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'accepted' },
    ] },
    { id: 13, waktu: '2026-09-03 16:10:00', user: 'Budi Santoso, S.Kom', aksi: 'Edit Profil Instansi', instansi: 'Diskominfo Kaltim', ip: '36.85.19.42', perubahan: [
        { field: 'Email Kontak', old: 'info@dkominfo.kaltimprov.go.id', new: 'admin@dkominfo.kaltimprov.go.id' },
        { field: 'Deskripsi', old: '-', new: 'Diperbarui deskripsi instansi.' },
    ] },
    { id: 14, waktu: '2026-09-03 09:52:00', user: 'Yoga Pratama', aksi: 'Tolak Pengajuan', instansi: 'BANKALTIMTARA', ip: '36.85.33.81', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'rejected' },
    ] },
    { id: 15, waktu: '2026-09-02 14:33:00', user: 'Siti Aminah, S.Kom', aksi: 'Tambah Bidang', instansi: 'RSUD Abdul Wahab Sjahranie', ip: '36.85.21.107', perubahan: [
        { field: 'Nama Bidang', old: '-', new: 'Rekam Medis Elektronik' },
        { field: 'Kuota Awal', old: '-', new: '4' },
    ] },
    { id: 16, waktu: '2026-09-02 08:15:00', user: 'Admin Pusat', aksi: 'Ubah Kuota', instansi: 'PT Pegadaian Cabang Samarinda', ip: '103.20.75.11', perubahan: [
        { field: 'Kuota Bidang Penilaian', old: '2', new: '3' },
    ] },
    { id: 17, waktu: '2026-09-01 13:48:00', user: 'Lina Marlina', aksi: 'Terima Pengajuan', instansi: 'Dinas Pendidikan Kaltim', ip: '114.122.200.15', perubahan: [
        { field: 'Status Pengajuan', old: 'pending', new: 'accepted' },
    ] },
    { id: 18, waktu: '2026-09-01 10:20:00', user: 'Rina Marlina', aksi: 'Undang Admin', instansi: 'BANKALTIMTARA', ip: '36.85.33.81', perubahan: [
        { field: 'Email Calon Admin', old: '-', new: 'yoga.p@bankaltimtara.co.id' },
    ] },
]);

const aksiOptions = [
    'Terima Pengajuan',
    'Tolak Pengajuan',
    'Tambah Walk-in',
    'Ubah Kuota',
    'Ubah Status Bidang',
    'Tambah Bidang',
    'Tambah Instansi',
    'Undang Admin',
    'Edit Profil Instansi',
];

const instansiOptions = computed(() => {
    const names = logList.value.map((log) => log.instansi);
    return [...new Set(names)];
});

const filteredLog = computed(() => {
    return logList.value.filter((log) => {
        const matchInstansi = !instansiFilter.value || log.instansi === instansiFilter.value;
        const matchAksi = !aksiFilter.value || log.aksi === aksiFilter.value;
        const matchDate = (!tanggalMulai.value || log.waktu.slice(0, 10) >= tanggalMulai.value) &&
                          (!tanggalSelesai.value || log.waktu.slice(0, 10) <= tanggalSelesai.value);
        return matchInstansi && matchAksi && matchDate;
    });
});

const aksiBadge = (aksi) => {
    if (aksi === 'Tolak Pengajuan') return 'badge-danger';
    if (aksi === 'Terima Pengajuan') return 'badge-success';
    if (aksi.startsWith('Ubah')) return 'badge-warning';
    return 'badge-info';
};

const detailLog = ref(null);

const showDetail = (log) => {
    detailLog.value = log;
};

const closeDetail = () => {
    detailLog.value = null;
};
</script>

<template>
    <Head title="Audit Log" />
    <SuperAdminLayout title="Audit Log">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Audit Log Sistem</h2>
            <p class="mt-1 text-sm text-ink-500">Jejak aktivitas seluruh user dan admin di semua instansi.</p>
        </div>

        <!-- Filter Bar -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="w-full lg:flex-1">
                    <select v-model="instansiFilter" class="field-input">
                        <option value="">Semua Instansi</option>
                        <option v-for="inst in instansiOptions" :key="inst" :value="inst">{{ inst }}</option>
                    </select>
                </div>
                <div class="w-full lg:w-56">
                    <select v-model="aksiFilter" class="field-input">
                        <option value="">Semua Jenis Aksi</option>
                        <option v-for="aksi in aksiOptions" :key="aksi" :value="aksi">{{ aksi }}</option>
                    </select>
                </div>
                <div class="w-full lg:w-44">
                    <input v-model="tanggalMulai" type="date" class="field-input" />
                </div>
                <div class="w-full lg:w-44">
                    <input v-model="tanggalSelesai" type="date" class="field-input" />
                </div>
            </form>
        </div>

        <!-- Audit Log Table -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-ink-900">Jejak Aktivitas</h3>
                <span class="badge badge-info">{{ filteredLog.length }} Entri</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">User / Admin</th>
                            <th class="px-4 py-3">Jenis Aksi</th>
                            <th class="px-4 py-3">Instansi Terkait</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="log in filteredLog" :key="log.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500 whitespace-nowrap">{{ log.waktu }}</td>
                            <td class="px-4 py-4 font-medium text-ink-900">{{ log.user }}</td>
                            <td class="px-4 py-4">
                                <span :class="aksiBadge(log.aksi)" class="badge">
                                    {{ log.aksi }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-ink-700">{{ log.instansi }}</td>
                            <td class="px-4 py-4 font-mono text-xs text-ink-500">{{ log.ip }}</td>
                            <td class="px-4 py-4 text-right">
                                <button @click="showDetail(log)" class="btn-secondary px-3 py-1.5 text-xs">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredLog.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada aktivitas yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Detail Log Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="detailLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-forest-950/50 backdrop-blur-sm" @click="closeDetail" />
                    <div class="relative w-full max-w-2xl rounded-3xl border border-white/70 bg-white/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div class="mb-6 flex items-start justify-between">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink-900">Detail Aktivitas</h3>
                                <p class="mt-1 text-sm text-ink-500">
                                    {{ detailLog.waktu }} · {{ detailLog.instansi }}
                                </p>
                            </div>
                            <button @click="closeDetail" class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-ink-500 hover:bg-ink-100 transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <!-- Log Metadata -->
                        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 rounded-2xl border border-ink-300/30 bg-surface p-4">
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">User / Admin</p>
                                <p class="mt-1 font-semibold text-ink-900">{{ detailLog.user }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Jenis Aksi</p>
                                <p class="mt-1">
                                    <span :class="aksiBadge(detailLog.aksi)" class="badge">{{ detailLog.aksi }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">IP Address</p>
                                <p class="mt-1 font-mono text-sm text-ink-800">{{ detailLog.ip }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Instansi Terkait</p>
                                <p class="mt-1 text-ink-800">{{ detailLog.instansi }}</p>
                            </div>
                        </div>

                        <!-- Perubahan Data -->
                        <h4 class="mb-3 font-display text-sm font-bold text-ink-900">Perubahan Data</h4>
                        <div class="overflow-hidden rounded-2xl border border-ink-300/30">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-ink-300/35 bg-surface text-xs uppercase tracking-wider text-ink-500">
                                    <tr>
                                        <th class="px-4 py-2.5">Field</th>
                                        <th class="px-4 py-2.5">Nilai Lama</th>
                                        <th class="px-4 py-2.5">Nilai Baru</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-300/20 bg-white/50">
                                    <tr v-for="(perubahan, index) in detailLog.perubahan" :key="index">
                                        <td class="px-4 py-3 font-medium text-ink-900">{{ perubahan.field }}</td>
                                        <td class="px-4 py-3 text-status-danger">
                                            <span class="inline-flex items-center rounded-md bg-status-danger/10 px-2 py-0.5 text-xs">{{ perubahan.old }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-status-success">
                                            <span class="inline-flex items-center rounded-md bg-status-success/10 px-2 py-0.5 text-xs">{{ perubahan.new }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="detailLog.perubahan.length === 0">
                                        <td colspan="3" class="px-4 py-6 text-center text-ink-500">
                                            Tidak ada data perubahan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button @click="closeDetail" class="btn-secondary">Tutup</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>
