<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    bidang: { type: Object, required: true },
});

const bidang = computed(() => props.bidang);
const statusLabel = computed(() => ({
    tersedia: 'Slot Tersedia',
    menipis: 'Kuota Menipis',
    'hampir-penuh': 'Hampir Penuh',
    penuh: 'Kuota Penuh',
}[bidang.value.status] ?? bidang.value.status));
const statusClass = computed(() => ['penuh', 'hampir-penuh'].includes(bidang.value.status) ? 'badge-danger' : bidang.value.status === 'menipis' ? 'badge-warning' : 'badge-success');
</script>

<template>
    <Head title="Detail Bidang" />
    <AppLayout title="Detail Bidang PKL">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Detail Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Info -->
                <div class="glass-panel p-6 sm:p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="font-display text-2xl font-bold text-ink-900">{{ bidang.nama }}</h2>
                            <p class="mt-1 text-sm text-ink-500">{{ bidang.instansi }} · Dibuat {{ bidang.created_at }}</p>
                        </div>
                        <span
                            :class="statusClass"
                            class="badge"
                        >
                            {{ statusLabel }}
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-2">Deskripsi Bidang</h3>
                            <p class="text-sm text-ink-800 leading-relaxed">{{ bidang.deskripsi }}</p>
                        </div>
                        <div class="border-t border-ink-300/30 pt-4">
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-2">Kualifikasi</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="jurusan in [...new Set((bidang.positions ?? []).flatMap((position) => position.jurusan ?? []))]" :key="jurusan" class="badge badge-info">
                                    {{ jurusan }}
                                </span>
                                <span v-if="!(bidang.positions ?? []).some((position) => position.jurusan?.length)" class="text-sm text-ink-500">
                                    Belum ada kualifikasi khusus.
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Posisi Table -->
                <div class="glass-panel p-6">
                    <h3 class="mb-4 font-display text-base font-bold text-ink-900">Posisi yang Dibuka</h3>
                    <div v-if="bidang.positions?.length" class="space-y-3">
                        <div v-for="position in bidang.positions" :key="position.id" class="flex items-center justify-between rounded-xl border border-ink-300/40 bg-white/50 p-4">
                            <div>
                                <p class="font-semibold text-ink-900">{{ position.nama }}</p>
                                <p class="text-xs text-ink-500">{{ (position.jurusan ?? []).join(', ') || 'Semua jurusan' }}</p>
                            </div>
                            <span class="badge badge-info">Kuota {{ position.terisi }}/{{ position.kuota }}</span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-ink-500">Belum ada posisi.</p>
                </div>
            </div>

            <!-- Right: Sidebar Card -->
            <div class="space-y-6">
                <div class="glass-card p-6 space-y-4 h-fit">
                    <h3 class="font-display text-base font-bold text-ink-900 border-b border-ink-300/30 pb-3">
                        Ringkasan Kuota
                    </h3>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Kuota</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ bidang.kuota_total }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Terisi</p>
                        <p class="mt-1 font-display text-2xl font-bold text-forest-700">{{ bidang.terisi_total }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Sisa Kuota</p>
                        <p class="mt-1 font-display text-2xl font-bold" :class="bidang.kuota_total - bidang.terisi_total > 0 ? 'text-status-success' : 'text-status-danger'">
                            {{ bidang.kuota_total - bidang.terisi_total }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-ink-300/30 space-y-2">
                        <Link :href="route('admin.bidang.edit', bidang.id)" class="btn-primary w-full text-center text-sm">
                            Edit Bidang
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>