<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { MapPin, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    division: { type: Object, required: true },
});

const location = computed(() => ({
    alamat: props.division?.agency?.address || props.division?.alamat_lengkap || props.division?.agency?.alamat_lengkap || '',
    latitude: props.division?.latitude ?? props.division?.agency?.latitude ?? null,
    longitude: props.division?.longitude ?? props.division?.agency?.longitude ?? null,
}));

const mapsUrl = computed(() => {
    if (props.division?.agency?.maps_url) {
        return props.division.agency.maps_url;
    }
    if (props.division?.agency?.maps_link) {
        return props.division.agency.maps_link;
    }
    if (location.value.latitude != null && location.value.longitude != null) {
        return `https://www.google.com/maps?q=${location.value.latitude},${location.value.longitude}`;
    }
    if (location.value.alamat) {
        return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent((props.division.instansi || '') + ' ' + location.value.alamat)}`;
    }
    return '';
});

const kualifikasi = computed(() => {
    const set = new Set();
    for (const p of props.division?.positions ?? []) {
        for (const k of p.kualifikasi ?? []) set.add(k);
    }
    return [...set];
});

const jurusan = computed(() => {
    const set = new Set();
    for (const j of props.division?.jurusan ?? []) set.add(j);
    for (const p of props.division?.positions ?? []) {
        for (const j of p.jurusan ?? []) set.add(j);
    }
    if (set.size === 0) {
        for (const j of props.division?.jurusan_tags ?? []) set.add(j);
    }
    return [...set];
});

const kuotaPenuh = computed(() => Number(props.division?.sisa_total ?? 0) <= 0);
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
                </div>

                <!-- Informasi PKL -->
                <div class="glass-panel p-6 sm:p-8">
                    <h2 class="mb-2 font-display text-xl font-bold text-ink-900">Informasi PKL</h2>
                    <p class="mt-4 text-sm leading-relaxed text-ink-700">
                        {{ division.deskripsi }}
                    </p>

                    <!-- Kualifikasi -->
                    <div v-if="kualifikasi.length" class="mt-6">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-500">Kualifikasi</h4>
                        <ul class="space-y-2 text-sm text-ink-700">
                            <li v-for="(kual, kidx) in kualifikasi" :key="kidx" class="flex items-center gap-2.5">
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
                    <div v-if="jurusan.length" class="mt-6">
                        <h4 class="mb-3 text-xs font-semibold uppercase tracking-wider text-ink-500">Jurusan yang Diutamakan</h4>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="(jur, jidx) in jurusan"
                                :key="jidx"
                                class="rounded-full bg-forest-50 px-3 py-1 text-xs font-medium text-forest-700"
                            >
                                {{ jur }}
                            </span>
                        </div>
                    </div>

                    <!-- Kuota -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between text-xs font-medium text-ink-500">
                            <span>{{ division.terisi_total }} / {{ division.kuota_total }} slot terisi</span>
                            <span class="font-semibold text-ink-700">{{ division.persentase }}%</span>
                        </div>
                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100">
                            <div
                                class="h-full rounded-full transition-all duration-500"
                                :class="kuotaPenuh ? 'bg-ink-400' : 'bg-status-success'"
                                :style="{ width: Math.min(100, Number(division.persentase ?? 0)) + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- CTA Daftar -->
                    <div class="mt-7 border-t border-ink-300/30 pt-6">
                        <Link
                            v-if="!kuotaPenuh"
                            :href="route('pengajuan.index', { division: division.id })"
                            class="btn-primary w-full py-3 px-6 text-sm font-semibold shadow-md"
                        >
                            Daftar Sekarang
                        </Link>
                        <span
                            v-else
                            class="block w-full cursor-not-allowed rounded-full bg-ink-100 py-3 px-6 text-center text-sm font-semibold text-ink-400"
                        >
                            Kuota Penuh
                        </span>
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
                            <span :class="kuotaPenuh ? 'badge badge-danger' : 'badge badge-success'">{{ kuotaPenuh ? 'Penuh' : 'Buka' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lokasi Instansi -->
                <div v-if="location.alamat || mapsUrl" class="glass-panel p-6 space-y-4 h-fit">
                    <h2 class="font-display text-lg font-bold text-ink-900 pb-3 border-b border-ink-300/30">
                        Lokasi Instansi
                    </h2>
                    <div class="flex items-start gap-3 text-sm">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700">
                            <MapPin :size="20" :stroke-width="1.8" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-ink-900">{{ props.division.instansi }}</p>
                            <p v-if="location.alamat" class="mt-1 text-xs leading-relaxed text-ink-600">
                                {{ location.alamat }}
                            </p>
                        </div>
                    </div>
                    <a
                        v-if="mapsUrl"
                        :href="mapsUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn-primary w-full text-center text-sm"
                    >
                        <ExternalLink :size="16" :stroke-width="2" />
                        Lihat di Peta
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>