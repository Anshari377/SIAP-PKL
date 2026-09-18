<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import BidangCard from '@/Components/BidangCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Search,
    Building2,
    LayoutGrid,
    Users,
    Database,
    Slice,
    SearchX,
    RotateCcw,
    Calendar,
} from 'lucide-vue-next';

const props = defineProps({
    divisions: { type: Array, default: () => [] },
    instansi: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({ search: '', instansi: '', status: '', tanggal_mulai: '', tanggal_selesai: '' }) },
});

const search = ref(props.filters.search || '');
const instansi = ref(props.filters.instansi || '');
const status = ref(props.filters.status || '');
const tanggalMulai = ref(props.filters.tanggal_mulai || '');
const tanggalSelesai = ref(props.filters.tanggal_selesai || '');

const hasActiveFilter = computed(
    () => search.value !== '' || instansi.value !== '' || status.value !== '' || tanggalMulai.value !== '' || tanggalSelesai.value !== ''
);

const statusOptions = [
    { value: '', label: 'Semua Status' },
    { value: 'tersedia', label: 'Tersedia (>50% slot)' },
    { value: 'menipis', label: 'Menipis (20-50% slot)' },
    { value: 'penuh', label: 'Penuh (<20% slot)' },
];

const statCards = computed(() => [
    { label: 'Total Bidang PKL', value: props.stats.total_bidang ?? 0, icon: LayoutGrid, hint: 'Bidang yang tersedia' },
    { label: 'Total Instansi', value: props.stats.total_instansi ?? 0, icon: Building2, hint: 'Penyelenggara PKL' },
    { label: 'Total Slot Tersisa', value: props.stats.total_slot_tersisa ?? 0, icon: Users, hint: 'Akumulasi semua bidang' },
    { label: 'Total Slot Terisi', value: props.stats.total_slot_terisi ?? 0, icon: Database, hint: 'Peserta diterima' },
]);

const dateRangeError = computed(() => {
    if (!tanggalMulai.value || !tanggalSelesai.value) return '';
    return tanggalSelesai.value < tanggalMulai.value
        ? 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.'
        : '';
});

const handleFilter = () => {
    const params = {
        search: search.value,
        instansi: instansi.value,
        status: status.value,
    };

    if (tanggalMulai.value) params.tanggal_mulai = tanggalMulai.value;
    if (tanggalSelesai.value) params.tanggal_selesai = tanggalSelesai.value;

    router.get(route('katalog.index'), params, { preserveState: true, preserveScroll: true });
};

const resetFilters = () => {
    search.value = '';
    instansi.value = '';
    status.value = '';
    tanggalMulai.value = '';
    tanggalSelesai.value = '';
    router.get(route('katalog.index'), {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <Head title="Katalog Bidang PKL" />
    <PublicLayout>
        <!-- Hero -->
        <section class="bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800 text-white">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
                <p class="text-xs font-semibold uppercase tracking-widest text-gold-400">
                    SIAP-PKL · Dinas Komunikasi dan Informatika Provinsi Kalimantan Timur
                </p>
                <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">
                    Katalog Bidang Praktik Kerja Lapangan
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-white/80 sm:text-base">
                    Jelajahi daftar bidang PKL beserta kuota, jurusan yang dicari, dan ketersediaannya. Untuk mendaftar, silakan masuk terlebih dahulu menggunakan akun Google Anda.
                </p>
            </div>
        </section>

        <!-- Statistik -->
        <section class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="glass-panel -mt-6 p-4 shadow-lg sm:p-5">
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <div
                        v-for="stat in statCards"
                        :key="stat.label"
                        class="flex items-center gap-3 rounded-2xl bg-white/70 p-4"
                    >
                        <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700">
                            <component :is="stat.icon" :size="22" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-display text-2xl font-extrabold leading-none text-ink-900">{{ stat.value }}</p>
                            <p class="mt-1 truncate text-xs font-medium text-ink-500">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Filter -->
        <section class="mx-auto max-w-6xl px-4 pt-8 sm:px-6">
            <form @submit.prevent="handleFilter" class="glass-panel rounded-3xl p-4 shadow-lg sm:p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 items-end">
                    <div class="relative">
                        <label class="block text-xs font-semibold text-ink-600 mb-1">Pencarian</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-ink-400">
                                <Search :size="18" :stroke-width="2" />
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari bidang, posisi..."
                                class="field-input pl-10 w-full"
                            />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-ink-600 mb-1">Instansi</label>
                        <label class="relative block">
                            <span class="sr-only">Filter Instansi</span>
                            <select v-model="instansi" class="field-input appearance-none pr-8 w-full">
                                <option value="">Semua Instansi</option>
                                <option v-for="nama in instansi" :key="nama" :value="nama">{{ nama }}</option>
                            </select>
                            <Building2 :size="16" :stroke-width="2" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink-400" />
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-ink-600 mb-1">Status Kuota</label>
                        <label class="relative block">
                            <span class="sr-only">Filter Status Kuota</span>
                            <select v-model="status" class="field-input appearance-none pr-8 w-full">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <Slice :size="16" :stroke-width="2" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-ink-400" />
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-ink-600 mb-1">Tanggal Mulai PKL</label>
                        <div class="relative">
                            <input
                                v-model="tanggalMulai"
                                type="date"
                                class="field-input pl-9 w-full text-xs"
                                title="Tanggal Mulai PKL"
                            />
                            <Calendar :size="16" :stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-ink-600 mb-1">Tanggal Selesai PKL</label>
                        <div class="relative">
                            <input
                                v-model="tanggalSelesai"
                                type="date"
                                :min="tanggalMulai"
                                class="field-input pl-9 w-full text-xs"
                                title="Tanggal Selesai PKL"
                            />
                            <Calendar :size="16" :stroke-width="2" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-ink-400" />
                        </div>
                        <p v-if="dateRangeError" class="mt-1 text-xs font-medium text-red-600">{{ dateRangeError }}</p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-end gap-2">
                    <button type="submit" :disabled="Boolean(dateRangeError)" class="btn-primary disabled:cursor-not-allowed disabled:opacity-50">
                        <Search :size="16" :stroke-width="2" />
                        Cari
                    </button>
                    <button
                        v-if="hasActiveFilter"
                        type="button"
                        @click="resetFilters"
                        class="btn-secondary px-3"
                        title="Atur ulang filter"
                    >
                        <RotateCcw :size="16" :stroke-width="2" />
                        Reset Filter
                    </button>
                </div>
            </form>
        </section>

        <!-- Grid Bidang -->
        <section class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div v-if="divisions.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                <BidangCard
                    v-for="item in divisions"
                    :key="item.id"
                    :item="item"
                    :detail-href="route('katalog.show', item.slug)"
                    :primary="{ label: 'Daftar Sekarang', href: route('auth.google') }"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="glass-panel rounded-3xl p-14 text-center">
                <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-ink-100 text-ink-400">
                    <SearchX :size="30" :stroke-width="1.8" />
                </div>
                <h2 class="mt-5 font-display text-xl font-bold text-ink-900">
                    Tidak ada bidang yang sesuai dengan pencarian Anda
                </h2>
                <p class="mx-auto mt-2 max-w-md text-sm text-ink-500">
                    Coba ubah kata kunci, ganti instansi, atau pilih status kuota lainnya untuk menemukan bidang PKL yang tepat bagi Anda.
                </p>
                <button @click="resetFilters" class="btn-secondary mt-6">
                    <RotateCcw :size="16" :stroke-width="2" />
                    Atur Ulang Filter
                </button>
            </div>
        </section>
    </PublicLayout>
</template>