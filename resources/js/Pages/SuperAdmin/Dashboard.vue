<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_instansi: 0,
            total_admin_aktif: 0,
            total_pengajuan_systemwide: 0,
            undangan_menunggu: 0,
        }),
    },
    aktivitasTerbaru: {
        type: Array,
        default: () => [],
    },
});

const aksiBadge = (aksi) => {
    if (!aksi) return 'badge-info';
    if (aksi.includes('Tolak')) return 'badge-danger';
    if (aksi.includes('Terima')) return 'badge-success';
    if (aksi.startsWith('Ubah')) return 'badge-warning';
    return 'badge-info';
};
</script>

<template>
    <Head title="Dashboard Super Admin" />
    <SuperAdminLayout title="Dashboard Super Admin">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Selamat Datang, Super Admin</h2>
            <p class="mt-1 text-sm text-ink-500">Ringkasan seluruh instansi, admin, dan aktivitas system-wide.</p>
        </div>

        <!-- 4 Stat Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Instansi Terdaftar</p>
                    <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">Aktif</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.total_instansi }}</p>
                <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 19 L16 15 L29 17 L44 9 L59 13 L74 6 L90 10 L105 3 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Admin Instansi Aktif</p>
                    <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">Terdaftar</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.total_admin_aktif }}</p>
                <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 17 L16 18 L29 12 L44 14 L59 8 L74 11 L90 5 L105 8 L119 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Pengajuan System-wide</p>
                    <span class="rounded-full bg-gold-500/15 px-2 py-0.5 text-[10px] font-semibold text-gold-500">Semua Instansi</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-gold-500">{{ stats.total_pengajuan_systemwide }}</p>
                <svg class="mt-3 h-6 w-full text-gold-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 12 L16 14 L29 9 L44 15 L59 10 L74 16 L90 11 L105 13 L119 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <div class="glass-card p-5 relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Undangan Menunggu</p>
                    <span class="rounded-full bg-status-warning/15 px-2 py-0.5 text-[10px] font-semibold text-gold-500">Perlu Ditindak</span>
                </div>
                <p class="mt-3 font-display text-3xl font-bold text-status-warning">{{ stats.undangan_menunggu }}</p>
                <svg class="mt-3 h-6 w-full text-gold-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                    <path d="M1 20 L16 16 L29 18 L44 12 L59 14 L74 8 L90 11 L105 4 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="mt-6 glass-panel p-6">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-ink-900">Aktivitas Terbaru</h3>
                <Link :href="route('superadmin.audit-log.index')" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-3 py-2">Waktu</th>
                            <th class="px-3 py-2">Admin/User</th>
                            <th class="px-3 py-2">Aksi</th>
                            <th class="px-3 py-2">Instansi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="item in aktivitasTerbaru" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-3 py-3 text-ink-500 whitespace-nowrap">{{ item.waktu }}</td>
                            <td class="px-3 py-3 font-medium text-ink-900">{{ item.user }}</td>
                            <td class="px-3 py-3">
                                <span :class="aksiBadge(item.aksi)" class="badge">
                                    {{ item.aksi }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-ink-700">{{ item.instansi }}</td>
                        </tr>
                        <tr v-if="aktivitasTerbaru.length === 0">
                            <td colspan="4" class="px-3 py-6 text-center text-ink-500">
                                Belum ada catatan aktivitas terbaru.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SuperAdminLayout>
</template>
