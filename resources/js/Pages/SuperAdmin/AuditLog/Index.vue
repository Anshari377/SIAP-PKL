<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    logList: {
        type: Array,
        default: () => [],
    },
});

const instansiFilter = ref('');
const aksiFilter = ref('');
const tanggalMulai = ref('');
const tanggalSelesai = ref('');

const aksiOptions = [
    'Terima Pengajuan',
    'Tolak Pengajuan',
    'Tambah Walk-in',
    'Ubah Kuota',
    'Ubah Status Bidang',
    'Tambah Bidang',
    'Tambah Instansi',
    'Undang Admin',
    'Edit Profil Instansi',
];

const instansiOptions = computed(() => {
    const names = props.logList.map((log) => log.instansi);
    return [...new Set(names)];
});

const filteredLog = computed(() => {
    return props.logList.filter((log) => {
        const matchInstansi = !instansiFilter.value || log.instansi === instansiFilter.value;
        const matchAksi = !aksiFilter.value || log.aksi === aksiFilter.value;
        const matchDate = (!tanggalMulai.value || log.waktu.slice(0, 10) >= tanggalMulai.value) &&
                          (!tanggalSelesai.value || log.waktu.slice(0, 10) <= tanggalSelesai.value);
        return matchInstansi && matchAksi && matchDate;
    });
});

const aksiBadge = (aksi) => {
    if (aksi === 'Tolak Pengajuan') return 'badge-danger';
    if (aksi === 'Terima Pengajuan') return 'badge-success';
    if (aksi.startsWith('Ubah')) return 'badge-warning';
    return 'badge-info';
};

const detailLog = ref(null);

const showDetail = (log) => {
    detailLog.value = log;
};

const closeDetail = () => {
    detailLog.value = null;
};
</script>

<template>
    <Head title="Audit Log" />
    <SuperAdminLayout title="Audit Log">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Audit Log Sistem</h2>
            <p class="mt-1 text-sm text-ink-500">Jejak aktivitas seluruh user dan admin di semua instansi.</p>
        </div>

        <!-- Filter Bar -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="w-full lg:flex-1">
                    <select v-model="instansiFilter" class="field-input">
                        <option value="">Semua Instansi</option>
                        <option v-for="inst in instansiOptions" :key="inst" :value="inst">{{ inst }}</option>
                    </select>
                </div>
                <div class="w-full lg:w-56">
                    <select v-model="aksiFilter" class="field-input">
                        <option value="">Semua Jenis Aksi</option>
                        <option v-for="aksi in aksiOptions" :key="aksi" :value="aksi">{{ aksi }}</option>
                    </select>
                </div>
                <div class="w-full lg:w-44">
                    <input v-model="tanggalMulai" type="date" class="field-input" />
                </div>
                <div class="w-full lg:w-44">
                    <input v-model="tanggalSelesai" type="date" class="field-input" />
                </div>
            </form>
        </div>

        <!-- Audit Log Table -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-ink-900">Jejak Aktivitas</h3>
                <span class="badge badge-info">{{ filteredLog.length }} Entri</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">User / Admin</th>
                            <th class="px-4 py-3">Jenis Aksi</th>
                            <th class="px-4 py-3">Instansi Terkait</th>
                            <th class="px-4 py-3">IP Address</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="log in filteredLog" :key="log.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500 whitespace-nowrap">{{ log.waktu }}</td>
                            <td class="px-4 py-4 font-medium text-ink-900">{{ log.user }}</td>
                            <td class="px-4 py-4">
                                <span :class="aksiBadge(log.aksi)" class="badge">
                                    {{ log.aksi }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-ink-700">{{ log.instansi }}</td>
                            <td class="px-4 py-4 font-mono text-xs text-ink-500">{{ log.ip }}</td>
                            <td class="px-4 py-4 text-right">
                                <button @click="showDetail(log)" class="btn-secondary px-3 py-1.5 text-xs">
                                    Lihat Detail
                                </button>
                            </td>
                        </tr>
                        <tr v-if="filteredLog.length === 0">
                            <td colspan="6" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada aktivitas yang cocok dengan filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Detail Log Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="detailLog" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-forest-950/50 backdrop-blur-sm" @click="closeDetail" />
                    <div class="relative w-full max-w-2xl rounded-3xl border border-white/70 bg-white/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div class="mb-6 flex items-start justify-between">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink-900">Detail Aktivitas</h3>
                                <p class="mt-1 text-sm text-ink-500">
                                    {{ detailLog.waktu }} · {{ detailLog.instansi }}
                                </p>
                            </div>
                            <button @click="closeDetail" class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-ink-500 hover:bg-ink-100 transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <!-- Log Metadata -->
                        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 rounded-2xl border border-ink-300/30 bg-surface p-4">
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">User / Admin</p>
                                <p class="mt-1 font-semibold text-ink-900">{{ detailLog.user }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Jenis Aksi</p>
                                <p class="mt-1">
                                    <span :class="aksiBadge(detailLog.aksi)" class="badge">{{ detailLog.aksi }}</span>
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">IP Address</p>
                                <p class="mt-1 font-mono text-sm text-ink-800">{{ detailLog.ip }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Instansi Terkait</p>
                                <p class="mt-1 text-ink-800">{{ detailLog.instansi }}</p>
                            </div>
                        </div>

                        <!-- Perubahan Data -->
                        <h4 class="mb-3 font-display text-sm font-bold text-ink-900">Perubahan Data</h4>
                        <div class="overflow-hidden rounded-2xl border border-ink-300/30">
                            <table class="w-full text-left text-sm">
                                <thead class="border-b border-ink-300/35 bg-surface text-xs uppercase tracking-wider text-ink-500">
                                    <tr>
                                        <th class="px-4 py-2.5">Field</th>
                                        <th class="px-4 py-2.5">Nilai Lama</th>
                                        <th class="px-4 py-2.5">Nilai Baru</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-ink-300/20 bg-white/50">
                                    <tr v-for="(perubahan, index) in detailLog.perubahan" :key="index">
                                        <td class="px-4 py-3 font-medium text-ink-900">{{ perubahan.field }}</td>
                                        <td class="px-4 py-3 text-status-danger">
                                            <span class="inline-flex items-center rounded-md bg-status-danger/10 px-2 py-0.5 text-xs">{{ perubahan.old }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-status-success">
                                            <span class="inline-flex items-center rounded-md bg-status-success/10 px-2 py-0.5 text-xs">{{ perubahan.new }}</span>
                                        </td>
                                    </tr>
                                    <tr v-if="detailLog.perubahan.length === 0">
                                        <td colspan="3" class="px-4 py-6 text-center text-ink-500">
                                            Tidak ada data perubahan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button @click="closeDetail" class="btn-secondary">Tutup</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>
