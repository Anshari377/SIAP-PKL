<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, formatDate } from '@/utils/statusLabel';

const props = defineProps({ pengajuan: { type: Array, default: () => [] } });
const search = ref('');
const statusFilter = ref('');

const filteredPengajuan = computed(() => {
    return props.pengajuan.map((item) => {
        const sekolahKampus =
            item.members?.[0]?.school ||
            item.user?.agency?.name ||
            item.user?.instansi ||
            item.school ||
            item.instansi ||
            '-';

        return {
            ...item,
            nama: item.members?.[0]?.name || item.user?.name || '-',
            sekolah: sekolahKampus,
            instansi: sekolahKampus,
            bidang: item.division?.nama ?? '-',
            posisi: item.position?.nama ?? item.division?.nama ?? 'Peserta PKL',
            tanggal: item.created_at,
        };
    }).filter((item) => {
        const matchSearch =
            !search.value ||
            item.nama.toLowerCase().includes(search.value.toLowerCase()) ||
            item.sekolah.toLowerCase().includes(search.value.toLowerCase());
        const matchStatus = !statusFilter.value || item.status === statusFilter.value;
        return matchSearch && matchStatus;
    });
});
</script>

<template>
    <Head title="Pengajuan Masuk" />
    <AdminLayout title="Pengajuan Masuk">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Pengajuan PKL Masuk</h2>
            <p class="mt-1 text-sm text-ink-500">Tinjau dan proses pengajuan praktik kerja lapangan yang masuk.</p>
        </div>

        <!-- Search & Filter Bar -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama pelamar..."
                        class="field-input"
                    />
                </div>
                <div class="w-full md:w-52">
                    <select v-model="statusFilter" class="field-input">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="accepted">Diterima</option>
                        <option value="revision">Perlu Revisi</option>
                        <option value="rejected">Ditolak</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Pengajuan Table -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Sekolah / Kampus</th>
                            <th class="px-4 py-3">Bidang</th>
                            <th class="px-4 py-3">Posisi</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="(item, index) in filteredPengajuan" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500">{{ index + 1 }}</td>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-ink-900">{{ item.nama }}</div>
                                <div class="text-xs text-ink-500">{{ item.email }}</div>
                            </td>
                            <td class="px-4 py-4 text-ink-700">{{ item.sekolah }}</td>
                            <td class="px-4 py-4 font-medium text-ink-800">{{ item.bidang }}</td>
                            <td class="px-4 py-4 text-ink-700">{{ item.posisi }}</td>
                            <td class="px-4 py-4 text-ink-500">{{ formatDate(item.tanggal) }}</td>
                            <td class="px-4 py-4">
                                <span :class="getStatusBadgeClass(item.status)" class="badge">
                                    {{ getStatusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link :href="route('admin.pengajuan.show', item.id)" class="btn-secondary px-3 py-1.5 text-xs">
                                        Detail
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredPengajuan.length === 0">
                            <td colspan="8" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada pengajuan yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>
</template>
