<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
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

const showCompleteModal = ref(false);
const selectedPeserta = ref(null);
const isSubmitting = ref(false);

const openCompleteModal = (item) => {
    selectedPeserta.value = item;
    showCompleteModal.value = true;
};

const closeCompleteModal = () => {
    if (isSubmitting.value) return;
    showCompleteModal.value = false;
    selectedPeserta.value = null;
};

const confirmComplete = () => {
    if (!selectedPeserta.value) return;
    isSubmitting.value = true;
    router.patch(
        route('admin.peserta.complete', selectedPeserta.value.application_id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                closeCompleteModal();
            },
            onFinish: () => {
                isSubmitting.value = false;
            },
        }
    );
};

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
                            <th class="px-4 py-3">Selesai</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
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
                            <td class="px-4 py-4 text-ink-500">
                                <span v-if="item.tanggal_selesai">{{ formatDate(item.tanggal_selesai) }}</span>
                                <span v-else class="text-ink-300">—</span>
                            </td>
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
                            <td class="px-4 py-4 text-right">
                                <button
                                    v-if="item.status === 'accepted'"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-forest-600/30 bg-forest-50 px-2.5 py-1.5 text-xs font-semibold text-forest-700 hover:bg-forest-600 hover:text-white transition shadow-sm"
                                    title="Tandai peserta telah menyelesaikan PKL"
                                    @click="openCompleteModal(item)"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Selesaikan
                                </button>
                                <span v-else class="text-xs font-medium text-ink-400">
                                    Telah Selesai
                                </span>
                            </td>
                        </tr>
                        <tr v-if="filteredPeserta.length === 0">
                            <td colspan="10" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada peserta yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Konfirmasi Selesaikan PKL -->
        <Modal :show="showCompleteModal" max-width="md" @close="closeCompleteModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-center gap-3">
                    <div class="grid h-12 w-12 place-items-center rounded-full bg-forest-100 text-forest-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-display text-lg font-bold text-ink-900">Selesaikan Peserta PKL</h3>
                        <p class="text-xs text-ink-500">Konfirmasi status penyelesaian praktik kerja lapangan</p>
                    </div>
                </div>

                <div v-if="selectedPeserta" class="mt-5 space-y-3 rounded-xl bg-forest-50/60 p-4 text-sm border border-forest-200/50">
                    <div class="flex justify-between">
                        <span class="text-ink-500 text-xs">Nama Peserta:</span>
                        <span class="font-semibold text-ink-900">{{ selectedPeserta.nama }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-500 text-xs">NIM / NISN:</span>
                        <span class="font-medium text-ink-700">{{ selectedPeserta.nim }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-500 text-xs">Bidang:</span>
                        <span class="font-medium text-forest-800">{{ selectedPeserta.bidang }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-ink-500 text-xs">Asal Instansi:</span>
                        <span class="font-medium text-ink-700">{{ selectedPeserta.instansi }}</span>
                    </div>
                    <div v-if="selectedPeserta.tanggal_pengajuan" class="flex justify-between">
                        <span class="text-ink-500 text-xs">Tanggal Pengajuan:</span>
                        <span class="font-medium text-ink-700">{{ selectedPeserta.tanggal_pengajuan }}</span>
                    </div>
                    <div v-if="selectedPeserta.tanggal_mulai" class="flex justify-between">
                        <span class="text-ink-500 text-xs">Periode PKL:</span>
                        <span class="font-medium text-ink-700">
                            {{ formatDate(selectedPeserta.tanggal_mulai) }} — {{ formatDate(selectedPeserta.tanggal_selesai) ?? '—' }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-amber-50 p-3.5 border border-amber-200/60 flex items-start gap-2.5">
                    <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Setelah diselesaikan, status peserta akan menjadi <strong>Selesai</strong> dan kuota pada bidang <strong>{{ selectedPeserta?.bidang }}</strong> akan <strong>otomatis berkurang</strong> (1 slot kuota dibebaskan kembali).
                    </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-xl border border-ink-300 px-4 py-2 text-sm font-semibold text-ink-700 hover:bg-ink-100 transition"
                        :disabled="isSubmitting"
                        @click="closeCompleteModal"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        class="btn-primary inline-flex items-center gap-2"
                        :disabled="isSubmitting"
                        @click="confirmComplete"
                    >
                        <svg v-if="isSubmitting" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>{{ isSubmitting ? 'Memproses...' : 'Ya, Selesaikan PKL' }}</span>
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
