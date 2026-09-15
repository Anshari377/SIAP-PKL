<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { getStatusLabel, getStatusBadgeClass } from '@/utils/statusLabel';

defineProps({
    stats: {
        type: Object,
        default: () => ({ lowongan_tersedia: 0, pendaftaran: 0, menunggu_verifikasi: 0, diterima: 0 }),
    },
    pendaftaranAktif: { type: Object, default: null },
    pengumuman: { type: Array, default: () => [] },
});
</script>

<template>
    <Head title="Home" />
    <AppLayout title="Home">
        <template #default>
            <div class="mb-6">
                <h2 class="font-display text-xl font-bold text-ink-900">Halo, Mahasiswa dan Siswa/Siswi  yang sedang PKL</h2>
                <p class="mt-1 text-sm text-ink-500">Selamat datang di Sistem Management PKL Diskominfo Kaltim.</p>
            </div>

            <!-- 4 Stat Cards in a row -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Bidang Tersedia -->
                <div class="glass-card p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Bidang Tersedia</p>
                        <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">+12%</span>
                    </div>
                    <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.lowongan_tersedia }}</p>
                    <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                        <path d="M1 19 L16 15 L29 17 L44 9 L59 13 L74 6 L90 10 L105 3 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Pendaftaran -->
                <div class="glass-card p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Pendaftaran</p>
                        <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">+5%</span>
                    </div>
                    <p class="mt-3 font-display text-3xl font-bold text-ink-900">{{ stats.pendaftaran }}</p>
                    <svg class="mt-3 h-6 w-full text-forest-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                        <path d="M1 17 L16 18 L29 12 L44 14 L59 8 L74 11 L90 5 L105 8 L119 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Menunggu Verifikasi -->
                <div class="glass-card p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Menunggu Verifikasi</p>
                        <span class="rounded-full bg-ink-500/10 px-2 py-0.5 text-[10px] font-semibold text-ink-500">0%</span>
                    </div>
                    <p class="mt-3 font-display text-3xl font-bold text-gold-500">{{ stats.menunggu_verifikasi }}</p>
                    <svg class="mt-3 h-6 w-full text-gold-500 opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                        <path d="M1 12 L16 14 L29 9 L44 15 L59 10 L74 16 L90 11 L105 13 L119 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <!-- Diterima -->
                <div class="glass-card p-5 relative overflow-hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Diterima</p>
                        <span class="rounded-full bg-forest-500/10 px-2 py-0.5 text-[10px] font-semibold text-forest-600">0%</span>
                    </div>
                    <p class="mt-3 font-display text-3xl font-bold text-status-success">{{ stats.diterima }}</p>
                    <svg class="mt-3 h-6 w-full text-status-success opacity-80" viewBox="0 0 120 24" fill="none" aria-hidden="true" preserveAspectRatio="none">
                        <path d="M1 20 L16 16 L29 18 L44 12 L59 14 L74 8 L90 11 L105 4 L119 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- 2 Columns Below -->
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Left: Pendaftaran Aktif -->
                <div class="glass-panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-base font-bold text-ink-900">Pendaftaran Aktif</h3>
                        <Link :href="route('riwayat.index')" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
                    </div>

                    <div v-if="pendaftaranAktif" class="flex items-center gap-4 rounded-xl border border-ink-300/40 bg-white/50 p-4 shadow-sm">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink-900">{{ pendaftaranAktif.judul }}</p>
                            <p class="text-xs text-ink-500 mt-0.5">{{ pendaftaranAktif.instansi }} · {{ pendaftaranAktif.tanggal }}</p>
                        </div>
                        <span :class="getStatusBadgeClass(pendaftaranAktif.status)" class="badge">
                            {{ getStatusLabel(pendaftaranAktif.status) }}
                        </span>
                    </div>
                    <div v-else class="rounded-xl border border-dashed border-ink-300/60 p-8 text-center">
                        <p class="text-sm text-ink-500">Belum ada pendaftaran aktif saat ini.</p>
                        <Link :href="route('bidang.index')" class="btn-primary mt-4 text-xs">Cari Bidang PKL</Link>
                    </div>
                </div>

                <!-- Right: Pengumuman Terbaru -->
                <div class="glass-panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-base font-bold text-ink-900">Pengumuman Terbaru</h3>
                        <Link href="#" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
                    </div>

                    <ul class="space-y-4">
                        <li class="flex items-start gap-3 rounded-lg p-2 transition hover:bg-white/40">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-forest-600" />
                            <div>
                                <p class="text-sm font-medium text-ink-900">Pembukaan Pendaftaran PKL Periode Gelombang II Diskominfo Kaltim</p>
                                <p class="text-xs text-ink-500 mt-0.5">01 September 2026</p>
                            </div>
                        </li>
                        <li class="flex items-start gap-3 rounded-lg p-2 transition hover:bg-white/40">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-forest-600" />
                            <div>
                                <p class="text-sm font-medium text-ink-900">Jadwal Orientasi dan Pembekalan Mahasiswa PKL Baru</p>
                                <p class="text-xs text-ink-500 mt-0.5">28 Agustus 2026</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

