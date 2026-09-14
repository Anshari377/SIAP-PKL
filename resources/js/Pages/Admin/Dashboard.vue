<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, formatDate } from '@/utils/statusLabel';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
    pengajuanTerbaru: { type: Array, default: () => [] },
    bidangAktif: { type: Array, default: () => [] },
});

const stats = computed(() => props.stats);
const pengajuanTerbaru = computed(() => props.pengajuanTerbaru.map((item) => ({
    ...item,
    nama: item.user?.name ?? '-',
    bidang: item.division?.nama ?? '-',
    tanggal: item.created_at,
})));
const bidangAktif = computed(() => props.bidangAktif.map((item) => ({
    ...item,
    nama: item.nama,
    kuota: item.quota,
})));
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout title="Dashboard Admin">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Selamat Datang, Admin Instansi</h2>
            <p class="mt-1 text-sm text-ink-500">Ringkasan aktivitas PKL Diskominfo Kaltim hari ini.</p>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Bidang</p>
                    <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">Aktif</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.total_bidang }}</p>
                <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 19 L16 15 L29 17 L44 9 L59 13 L74 6 L90 10 L105 3 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Pengajuan Baru</p>
                    <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">+3</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.pengajuan_baru }}</p>
                <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 17 L16 18 L29 12 L44 14 L59 8 L74 11 L90 5 L105 8 L119 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Menunggu Verifikasi</p>
                    <span class="rounded-full bg-gold-500/15 px-2 py-0.5 text-[10px] font-semibold text-gold-500">Perlu Ditinjau</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-gold-500">{{ stats.menunggu_verifikasi }}</p>
                <svg class="mt-3 h-6 w-full text-gold-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 12 L16 14 L29 9 L44 15 L59 10 L74 16 L90 11 L105 13 L119 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Peserta Aktif</p>
                    <span class="rounded-full bg-status-success/10 px-2 py-0.5 text-[10px] font-semibold text-status-success">On Progress</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-status-success">{{ stats.peserta_aktif }}</p>
                <svg class="mt-3 h-6 w-full text-status-success opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 20 L16 16 L29 18 L44 12 L59 14 L74 8 L90 11 L105 4 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <!-- 2 Columns Below -->
        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Left: Pengajuan Terbaru -->
            <div class="glass-panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-ink-900">Pengajuan Terbaru</h3>
                    <Link :href="route('admin.pengajuan.index')" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-left text-sm">
                        <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                            <tr>
                                <th class="px-3 py-2">Nama</th>
                                <th class="px-3 py-2">Posisi</th>
                                <th class="px-3 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ink-300/20">
                            <tr v-for="item in pengajuanTerbaru" :key="item.id" class="transition hover:bg-forest-50/60">
                                <td class="px-3 py-3 font-medium text-ink-900">{{ item.nama }}</td>
                                <td class="px-3 py-3 text-ink-700">{{ item.posisi }}</td>
                                <td class="px-3 py-3">
                                    <span :class="getStatusBadgeClass(item.status)" class="badge">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Bidang Aktif -->
            <div class="glass-panel p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-ink-900">Bidang Aktif</h3>
                    <Link :href="route('admin.bidang.index')" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
                </div>

                <ul class="space-y-4">
                    <li v-for="item in bidangAktif" :key="item.id" class="rounded-xl border border-ink-300/40 bg-white/50 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-ink-900">{{ item.nama }}</p>
                            <span class="text-xs font-medium text-ink-500">{{ item.terisi }}/{{ item.kuota }}</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100">
                            <div
                                class="h-full rounded-full bg-forest-500 transition-all"
                                :style="{ width: `${(item.terisi / item.kuota) * 100}%` }"
                            />
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
