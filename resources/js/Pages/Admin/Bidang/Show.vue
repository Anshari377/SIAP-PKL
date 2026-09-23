<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
    <AdminLayout title="Detail Bidang PKL">
        <div class="mb-4">
            <Link :href="route('admin.bidang.index')" class="inline-flex items-center gap-1 text-sm font-medium text-forest-700 hover:underline">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Kembali ke Daftar Bidang
            </Link>
        </div>

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
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-2">Jurusan yang Dicari</h3>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="jurusan in (bidang.jurusan ?? [])" :key="jurusan" class="badge badge-info">
                                    {{ jurusan }}
                                </span>
                                <span v-if="!(bidang.jurusan ?? []).length" class="text-sm text-ink-500">
                                    Terbuka untuk semua jurusan.
                                </span>
                            </div>
                        </div>

                        <!-- Sub-Posisi PKL -->
                        <div class="border-t border-ink-300/30 pt-4">
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-3">Sub-Posisi PKL</h3>
                            <div v-if="(bidang.positions ?? []).length === 0" class="text-sm text-ink-500 italic">
                                Belum ada sub-posisi yang ditambahkan.
                            </div>
                            <div v-else class="space-y-2">
                                <div
                                    v-for="pos in bidang.positions"
                                    :key="pos.id"
                                    class="rounded-lg border border-ink-300/40 bg-white/60 py-2.5 px-3.5"
                                >
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold text-sm text-ink-900">{{ pos.nama }}</p>
                                        <!-- <span v-if="pos.kuota" class="shrink-0 badge badge-info text-xs">Kuota: {{ pos.kuota }}</span> -->
                                    </div>
                                    <!-- <p v-if="pos.deskripsi" class="mt-1.5 text-xs text-ink-600 leading-relaxed">{{ pos.deskripsi }}</p>
                                    <div v-if="(pos.jurusan ?? []).length" class="mt-2 flex flex-wrap gap-1.5">
                                        <span v-for="j in (pos.jurusan ?? [])" :key="j" class="badge badge-info text-xs">{{ j }}</span>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
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
    </AdminLayout>
</template>