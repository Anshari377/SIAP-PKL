<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Hourglass } from 'lucide-vue-next';
import { getStatusLabel, getStatusBadgeClass, formatDate, isPastEndDate } from '@/utils/statusLabel';

const props = defineProps({
    peserta: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) },
});
const stats = computed(() => props.stats);
const search = ref('');
const statusFilter = ref('');

const filteredPeserta = computed(() => {
    return props.peserta.filter((item) => {
        const matchSearch = !search.value || item.nama.toLowerCase().includes(search.value.toLowerCase());
        const matchStatus = !statusFilter.value || item.status === statusFilter.value;
        return matchSearch && matchStatus;
    });
});
</script>

<template>
    <Head title="Peserta PKL" />
    <AdminLayout title="Peserta PKL">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Peserta Praktik Kerja Lapangan</h2>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <p class="mt-1 text-sm text-ink-500">Daftar peserta PKL yang sedang berlangsung atau telah selesai.</p>
                <Link :href="route('admin.peserta.walk-in.create')" class="btn-primary">Registrasi Walk-in</Link>
            </div>
        </div>

        <!-- 3 Stat Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
            <div class="glass-card p-5">
                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Aktif</p>
                <p class="mt-2 font-display text-3xl font-bold text-forest-700">{{ stats.total_aktif }}</p>
                <p class="mt-1 text-xs text-ink-500">Sedang menjalani PKL</p>
            </div>
            <div class="glass-card p-5">
                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Selesai</p>
                <p class="mt-2 font-display text-3xl font-bold text-ink-900">{{ stats.selesai }}</p>
                <p class="mt-1 text-xs text-ink-500">Telah menyelesaikan PKL</p>
            </div>
            <div class="glass-card p-5">
                <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Baru Bulan Ini</p>
                <p class="mt-2 font-display text-3xl font-bold text-gold-500">{{ stats.baru_bulan_ini }}</p>
                        <p class="mt-1 text-xs text-ink-500">Pengajuan pada bulan berjalan</p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama peserta..."
                        class="field-input"
                    />
                </div>
                <div class="w-full md:w-52">
                    <select v-model="statusFilter" class="field-input">
                        <option value="">Semua Status</option>
                        <option value="accepted">Aktif</option>
                        <option value="completed">Selesai</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Peserta Table -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[780px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Asal Instansi</th>
                            <th class="px-4 py-3">Bidang</th>
                            <th class="px-4 py-3">Posisi</th>
                            <th class="px-4 py-3">Mulai</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="(item, index) in filteredPeserta" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500">{{ index + 1 }}</td>
                            <td class="px-4 py-4 font-semibold text-ink-900">{{ item.nama }}</td>
                            <td class="px-4 py-4 text-ink-700">{{ item.nim }}</td>
                            <td class="px-4 py-4 text-ink-700">{{ item.instansi }}</td>
                            <td class="px-4 py-4 font-medium text-ink-800">{{ item.bidang }}</td>
                            <td class="px-4 py-4 text-ink-700">{{ item.posisi }}</td>
                            <td class="px-4 py-4 text-ink-500">{{ formatDate(item.tanggal_mulai) }}</td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span :class="getStatusBadgeClass(item.status)" class="badge">
                                        {{ getStatusLabel(item.status) }}
                                    </span>
                                    <span
                                        v-if="item.status === 'accepted' && isPastEndDate(item.tanggal_selesai || item.end_date)"
                                        class="badge badge-neutral cursor-help"
                                        title="Masa PKL sudah berakhir, menunggu pembaruan status otomatis oleh sistem."
                                    >
                                        <Hourglass :size="14" :stroke-width="2" />
                                        Menunggu pembaruan status
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredPeserta.length === 0">
                            <td colspan="8" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada peserta yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
