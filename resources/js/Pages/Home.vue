<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import TimelineStatus from '@/Components/TimelineStatus.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, getTimelineSteps } from '@/utils/statusLabel';

const props = defineProps({
    pendaftaranAktif: { type: Object, default: null },
});

const steps = computed(() => {
    const p = props.pendaftaranAktif;
    if (!p) return [];
    return getTimelineSteps({
        status: p.status,
        created_at: p.tanggal,
        updated_at: null,
    });
});
</script>

<template>
    <Head title="Home" />
    <AppLayout title="Home">
        <template #default>
            <div class="mb-6">
                <h2 class="font-display text-xl font-bold text-ink-900">Halo, Mahasiswa dan Siswa/Siswi yang sedang PKL</h2>
                <p class="mt-1 text-sm text-ink-500">Selamat datang di Sistem Management PKL Diskominfo Samarinda.</p>
            </div>

            <!-- Status Pengajuan Aktif + Timeline -->
            <div v-if="pendaftaranAktif" class="glass-panel p-6">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="font-display text-base font-bold text-ink-900">Status Pengajuan Aktif</h3>
                    <Link :href="route('status.index')" class="text-xs font-semibold text-forest-700 hover:text-forest-800 hover:underline transition">Lihat detail</Link>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl bg-white/70 border border-ink-100/80 shadow-sm">
                    <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-forest-50 text-forest-700 border border-forest-100">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-ink-900">{{ pendaftaranAktif.judul }}</p>
                            <p class="mt-0.5 text-xs text-ink-500 font-medium">{{ pendaftaranAktif.instansi }} · {{ pendaftaranAktif.tanggal }}</p>
                        </div>
                    </div>
                    <span :class="getStatusBadgeClass(pendaftaranAktif.status)" class="badge shrink-0 self-start sm:self-center">
                        {{ getStatusLabel(pendaftaranAktif.status) }}
                    </span>
                </div>

                <div class="mt-6 border-t border-ink-200/50 pt-6">
                    <h4 class="mb-4 text-xs font-semibold uppercase tracking-wider text-ink-500">Progres Pengajuan</h4>
                    <TimelineStatus :steps="steps" />
                </div>
            </div>

            <!-- CTA: Belum ada pengajuan aktif -->
            <div v-else class="glass-panel p-8 sm:p-12 text-center">
                <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl bg-forest-50 text-forest-700 border border-forest-100">
                    <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                    </svg>
                </div>
                <h3 class="font-display text-lg font-bold text-ink-900">Belum Ada Pengajuan PKL</h3>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-500">
                    Anda belum memiliki pengajuan PKL aktif. Jelajahi bidang PKL yang tersedia dan mulailah pendaftaran Anda sekarang.
                </p>
                <div class="mt-6">
                    <Link :href="route('bidang.index')" class="btn-primary px-6 py-2.5 text-sm font-semibold shadow-sm">
                        Mulai Pendaftaran PKL Sekarang
                    </Link>
                </div>
            </div>
        </template>
    </AppLayout>
</template>