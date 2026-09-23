<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { MapPin, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    division: { type: Object, required: true },
});

const isLoggedIn = computed(() => Boolean(usePage().props.auth?.user));

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

const statusMeta = (division) => {
    const sisa = Number(division.sisa_total ?? 0);
    const quota = Number(division.kuota_total ?? 0);

    if (sisa <= 0 || quota <= 0) {
        return {
            label: 'Kuota Penuh',
            badge: 'bg-ink-300/25 text-ink-500',
            bar: 'bg-ink-400',
        };
    }

    const sisaPct = (sisa / quota) * 100;

    if (sisaPct > 50) {
        return { label: 'Buka / Tersedia', badge: 'badge-success', bar: 'bg-status-success' };
    }

    if (sisaPct >= 20) {
        return { label: 'Kuota Menipis', badge: 'badge-warning', bar: 'bg-gold-500' };
    }

    return { label: 'Hampir Penuh', badge: 'badge-danger', bar: 'bg-status-danger' };
};
</script>

<template>
    <Head :title="props.division.nama" />
    <PublicLayout>
        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 sm:py-10">
            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-2 text-sm text-ink-500">
                <Link :href="route('katalog.index')" class="hover:text-forest-700 hover:underline">Katalog Bidang PKL</Link>
                <span>&gt;</span>
                <span class="font-medium text-ink-900">Detail Bidang</span>
            </nav>

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
                                <h1 class="font-display text-2xl font-bold text-ink-900">{{ props.division.nama }}</h1>
                                <p class="mt-1 text-sm font-medium text-forest-700">{{ props.division.instansi }}</p>
                            </div>
                        </div>
                        <p class="mt-5 text-sm leading-relaxed text-ink-700">
                            {{ props.division.deskripsi }}
                        </p>
                    </div>

                    <!-- Posisi PKL Tersedia -->
                    <div>
                        <h2 class="mb-4 font-display text-xl font-bold text-ink-900">Posisi PKL Tersedia</h2>

                        <div class="space-y-5">
                            <div v-for="(posisi, idx) in props.division.positions" :key="posisi.id" class="glass-panel p-6 sm:p-7">
                                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-ink-300/30">
                                    <div class="flex items-center gap-3">
                                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-forest-600/10 text-sm font-bold text-forest-700">
                                            {{ idx + 1 }}
                                        </span>
                                        <h3 class="font-display text-lg font-bold text-ink-900">{{ posisi.nama }}</h3>
                                    </div>
                                    <span v-if="posisi.terisi >= posisi.kuota" class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Kuota Penuh
                                    </span>
                                    <span v-else class="badge badge-success font-medium">
                                        Kuota {{ posisi.terisi }}/{{ posisi.kuota }}
                                    </span>
                                </div>

                                <p class="mt-4 text-sm leading-relaxed text-ink-700">{{ posisi.deskripsi }}</p>

                                <!-- Kualifikasi -->
                                <div v-if="posisi.kualifikasi?.length" class="mt-5">
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
                                <div v-if="posisi.jurusan?.length" class="mt-5">
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
                                <span class="ml-4 text-right font-semibold text-ink-900">{{ props.division.instansi }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-ink-500">Kategori Keahlian</span>
                                <span class="font-semibold text-ink-900">{{ props.division.kategori || 'Teknologi Informasi' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-ink-500">Total Kuota</span>
                                <span class="font-bold text-forest-700">{{ props.division.terisi_total }} / {{ props.division.kuota_total }} Terisi</span>
                            </div>

                            <div>
                                <div class="flex items-center justify-between text-xs font-medium text-ink-500">
                                    <span>Slot tersisa: {{ props.division.sisa_total }}</span>
                                    <span class="font-semibold text-ink-700">{{ props.division.persentase }}%</span>
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100">
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="statusMeta(props.division).bar"
                                        :style="{ width: Math.min(100, Number(props.division.persentase ?? 0)) + '%' }"
                                    ></div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-ink-500">Status</span>
                                <span :class="['badge', statusMeta(props.division).badge]">
                                    {{ statusMeta(props.division).label }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-ink-300/30">
                            <a
                                v-if="!isLoggedIn"
                                :href="route('login')"
                                class="btn-primary w-full text-center text-sm"
                            >
                                Daftar Sekarang
                            </a>
                            <Link
                                v-else
                                :href="route('pengajuan.index', { division: props.division.id })"
                                class="btn-primary w-full text-center text-sm"
                            >
                                Daftar Sekarang
                            </Link>
                            <p v-if="!isLoggedIn" class="mt-3 text-center text-[11px] leading-relaxed text-ink-500">
                                Anda akan diarahkan ke halaman login Google untuk melanjutkan pendaftaran.
                            </p>
                            <p v-else class="mt-3 text-center text-[11px] leading-relaxed text-ink-500">
                                Anda akan diarahkan ke halaman pengajuan untuk bidang ini.
                            </p>
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
        </div>
    </PublicLayout>
</template>