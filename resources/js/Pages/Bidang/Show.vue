<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    division: { type: Object, required: true },
});

const showKuotaPenuh = ref(false);
const penuhNama = ref('');

const handleDaftar = (posisi) => {
    if (posisi.penuh) {
        penuhNama.value = posisi.nama;
        showKuotaPenuh.value = true;
        return;
    }

    router.get(route('pengajuan.index'), { division: props.division.id });
};
</script>

<template>
    <Head :title="division.nama" />
    <AppLayout title="Detail Bidang">
        <!-- Breadcrumb -->
        <nav class="mb-6 flex items-center gap-2 text-sm text-ink-500">
            <Link :href="route('bidang.index')" class="hover:text-forest-700 hover:underline">Bidang PKL</Link>
            <span>&gt;</span>
            <span class="font-medium text-ink-900">Detail Bidang</span>
        </nav>

        <!-- 2 Columns Layout -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left Column (2/3 width) -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Bidang Header -->
                <div class="glass-panel p-6 sm:p-8">
                    <div class="flex items-start gap-4">
                        <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-forest-600/10 text-forest-700">
                            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="font-display text-2xl font-bold text-ink-900">{{ division.nama }}</h1>
                            <p class="mt-1 text-sm font-medium text-forest-700">{{ division.instansi }}</p>
                        </div>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed text-ink-700">
                        {{ division.deskripsi }}
                    </p>
                </div>

                <!-- Posisi PKL Tersedia -->
                <div>
                    <h2 class="mb-4 font-display text-xl font-bold text-ink-900">Posisi PKL Tersedia</h2>

                    <div class="space-y-5">
                        <div v-for="(posisi, idx) in division.positions" :key="posisi.id" class="glass-panel p-6 sm:p-7">
                            <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-ink-300/30">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-forest-600/10 text-sm font-bold text-forest-700">
                                        {{ idx + 1 }}
                                    </span>
                                    <h3 class="font-display text-lg font-bold text-ink-900">{{ posisi.nama }}</h3>
                                </div>
                                <span v-if="posisi.penuh" class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                    Kuota Penuh
                                </span>
                                <span v-else class="badge badge-success font-medium">
                                    Kuota {{ posisi.terisi }}/{{ posisi.kuota }}
                                </span>
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-ink-700">{{ posisi.deskripsi }}</p>

                            <!-- Kualifikasi -->
                            <div class="mt-5">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Kualifikasi</h4>
                                <ul class="space-y-2 text-sm text-ink-700">
                                    <li v-for="(kual, kidx) in posisi.kualifikasi" :key="kidx" class="flex items-center gap-2.5">
                                        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full bg-forest-600/10 text-forest-600">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </span>
                                        <span>{{ kual }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Jurusan yang Diutamakan -->
                            <div class="mt-5">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-ink-500 mb-2">Jurusan yang Diutamakan</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(jur, jidx) in posisi.jurusan"
                                        :key="jidx"
                                        class="rounded-full bg-forest-50 px-3 py-1 text-xs font-medium text-forest-700"
                                    >
                                        {{ jur }}
                                    </span>
                                </div>
                            </div>

                            <button
                                v-if="!posisi.penuh"
                                @click="handleDaftar(posisi)"
                                class="btn-primary mt-6 w-full sm:w-auto py-3 px-6 text-sm font-semibold shadow-md"
                            >
                                Daftar Sekarang
                            </button>
                            <button
                                v-else
                                disabled
                                class="mt-6 w-full sm:w-auto cursor-not-allowed rounded-full bg-ink-100 py-3 px-6 text-sm font-semibold text-ink-400"
                            >
                                Kuota Penuh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column (1/3 width) -->
            <div class="space-y-6">
                <div class="glass-panel p-6 space-y-5 h-fit">
                    <h2 class="font-display text-lg font-bold text-ink-900 pb-3 border-b border-ink-300/30">
                        Informasi Bidang
                    </h2>

                    <div class="space-y-4 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-ink-500">Instansi</span>
                            <span class="ml-4 text-right font-semibold text-ink-900">{{ division.instansi }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-ink-500">Kategori Keahlian</span>
                            <span class="font-semibold text-ink-900">{{ division.kategori || 'Teknologi Informasi' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-ink-500">Penempatan</span>
                            <span class="font-semibold text-ink-900">Samarinda, Kaltim</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-ink-500">Total Kuota</span>
                            <span class="font-bold text-forest-700">{{ division.terisi_total }} / {{ division.kuota_total }} Terisi</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-ink-500">Status</span>
                            <span class="badge badge-success">Buka</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Soft Alert Modal — Kuota Sudah Penuh -->
        <Modal :show="showKuotaPenuh" max-width="sm" @close="showKuotaPenuh = false">
            <div class="rounded-2xl px-8 py-10 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-amber-100">
                    <svg viewBox="0 0 24 24" class="h-8 w-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                </div>

                <h3 class="mt-5 font-display text-xl font-bold text-ink-900">Kuota Sudah Penuh</h3>
                <p class="mt-3 text-sm leading-relaxed text-ink-500">
                    Maaf, kuota untuk posisi {{ penuhNama }} sudah terisi penuh.
                    Silakan pilih posisi lain yang masih tersedia.
                </p>

                <button
                    @click="showKuotaPenuh = false"
                    class="mt-7 w-full rounded-full bg-amber-100 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-200"
                >
                    Mengerti
                </button>
            </div>
        </Modal>
    </AppLayout>
</template>
