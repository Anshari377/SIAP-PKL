<script setup>
import { Link } from '@inertiajs/vue3';
import {
    Building2,
    ClipboardList,
    Code2,
    Network,
    Shield,
    GraduationCap,
    HeartPulse,
    LineChart,
    Map,
} from 'lucide-vue-next';

defineProps({
    item: { type: Object, required: true },
    detailHref: { type: String, default: '' },
    primary: { type: Object, default: null },
});

const categoryIcons = {
    'Teknologi Informasi': Code2,
    'Infrastruktur Jaringan': Network,
    'Keamanan Siber': Shield,
    'Administrasi': ClipboardList,
    'Pendidikan': GraduationCap,
    'Kesehatan': HeartPulse,
    'Perencanaan': LineChart,
    'Sistem Informasi Geografis': Map,
};

const iconFor = (kategori) => categoryIcons[kategori] || ClipboardList;

const statusMeta = (item) => {
    const sisa = Number(item.sisa_total ?? 0);
    const quota = Number(item.kuota_total ?? 0);

    if (sisa <= 0 || quota <= 0) {
        return {
            label: 'Kuota Penuh',
            badge: 'bg-ink-300/25 text-ink-500',
            bar: 'bg-ink-400',
        };
    }

    const sisaPct = (sisa / quota) * 100;

    if (sisaPct > 50) {
        return {
            label: 'Slot Tersedia',
            badge: 'badge-success',
            bar: 'bg-status-success',
        };
    }

    if (sisaPct >= 20) {
        return {
            label: 'Kuota Menipis',
            badge: 'badge-warning',
            bar: 'bg-gold-500',
        };
    }

    return {
        label: 'Hampir Penuh',
        badge: 'badge-danger',
        bar: 'bg-status-danger',
    };
};
</script>

<template>
    <article class="glass-card flex flex-col gap-5 rounded-3xl p-6 transition hover:-translate-y-1 hover:border-forest-500/40 hover:shadow-card">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <div class="mb-3 inline-grid h-12 w-12 place-items-center rounded-2xl bg-forest-600/10 text-forest-700">
                    <component :is="iconFor(item.kategori)" :size="24" :stroke-width="1.8" />
                </div>
                <Link
                    :href="detailHref"
                    class="font-display text-lg font-bold leading-snug text-ink-900 transition hover:text-forest-700"
                >
                    {{ item.nama }}
                </Link>
                <p class="mt-1 flex items-center gap-1.5 text-sm text-ink-500">
                    <Building2 :size="14" :stroke-width="2" class="shrink-0" />
                    {{ item.instansi }}
                </p>
            </div>
            <span :class="['badge', statusMeta(item).badge]" class="whitespace-nowrap">
                {{ statusMeta(item).label }}
            </span>
        </div>

        <!-- Progress Kuota -->
        <div>
            <div class="flex items-center justify-between text-xs font-medium text-ink-500">
                <span>{{ item.terisi_total }} / {{ item.kuota_total }} slot terisi</span>
                <span class="font-semibold text-ink-700">{{ item.persentase }}%</span>
            </div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-ink-100">
                <div
                    class="h-full rounded-full transition-all duration-500"
                    :class="statusMeta(item).bar"
                    :style="{ width: Math.min(100, Number(item.persentase ?? 0)) + '%' }"
                ></div>
            </div>
        </div>

        <!-- Jurusan -->
        <div v-if="item.jurusan_tags?.length" class="flex flex-wrap gap-1.5">
            <span
                v-for="(jur, jidx) in item.jurusan_tags"
                :key="jidx"
                class="rounded-full border border-forest-100 bg-forest-50 px-2.5 py-1 text-xs font-medium text-forest-700"
            >
                {{ jur }}
            </span>
        </div>

        <!-- Aksi -->
        <div class="mt-auto grid grid-cols-2 gap-3 border-t border-ink-300/30 pt-5">
            <Link :href="detailHref" class="btn-secondary px-3 py-2.5 text-xs">
                Lihat Detail
            </Link>
            <Link
                v-if="primary"
                :href="primary.href"
                class="btn-primary px-3 py-2.5 text-xs"
            >
                {{ primary.label }}
            </Link>
            <span
                v-else
                class="inline-flex items-center justify-center rounded-lg border border-dashed border-ink-300 px-3 py-2.5 text-xs text-ink-400"
            >
                Tidak tersedia
            </span>
        </div>
    </article>
</template>