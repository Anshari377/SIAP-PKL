<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const search = ref('');
const bidang = ref('');

const bidangList = ref([
    { id: 1, nama: 'Aplikasi dan Layanan E-Government', instansi: 'Diskominfo Kaltim', kuota_total: 8, terisi_total: 5, status: 'aktif' },
    { id: 2, nama: 'Infrastruktur Jaringan dan Server', instansi: 'Diskominfo Kaltim', kuota_total: 6, terisi_total: 4, status: 'aktif' },
    { id: 3, nama: 'Sekretariat dan Tata Usaha', instansi: 'Diskominfo Kaltim', kuota_total: 4, terisi_total: 3, status: 'aktif' },
    { id: 4, nama: 'Diseminasi Informasi Publik', instansi: 'Diskominfo Kaltim', kuota_total: 5, terisi_total: 2, status: 'aktif' },
    { id: 5, nama: 'Pengelolaan Data dan Statistik', instansi: 'Diskominfo Kaltim', kuota_total: 3, terisi_total: 3, status: 'penuh' },
    { id: 6, nama: 'Hubungan Masyarakat dan Media', instansi: 'Diskominfo Kaltim', kuota_total: 4, terisi_total: 0, status: 'nonaktif' },
]);

const filteredBidang = computed(() => {
    return bidangList.value.filter((item) => {
        const matchSearch = !search.value || item.nama.toLowerCase().includes(search.value.toLowerCase());
        const matchBidang = !bidang.value || item.nama === bidang.value;
        return matchSearch && matchBidang;
    });
});

const statusBadge = (status) => {
    switch (status) {
        case 'aktif': return 'badge-success';
        case 'penuh': return 'badge-warning';
        case 'nonaktif': return 'badge-danger';
        default: return 'badge-info';
    }
};

const statusLabel = (status) => {
    switch (status) {
        case 'aktif': return 'Aktif';
        case 'penuh': return 'Penuh';
        case 'nonaktif': return 'Nonaktif';
        default: return status;
    }
};

const handleDelete = (item) => {
    if (confirm(`Hapus bidang "${item.nama}"?`)) {
        bidangList.value = bidangList.value.filter((b) => b.id !== item.id);
    }
};
</script>

<template>
    <Head title="Kelola Bidang" />
    <AppLayout title="Kelola Bidang PKL">
        <!-- Header Action -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-xl font-bold text-ink-900">Daftar Bidang PKL</h2>
                <p class="mt-1 text-sm text-ink-500">Kelola seluruh bidang praktik kerja lapangan instansi Anda.</p>
            </div>
            <Link :href="route('admin.bidang.create')" class="btn-primary w-full sm:w-auto shrink-0">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                + Buat Bidang
            </Link>
        </div>

        <!-- Search & Filter Bar -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama bidang..."
                        class="field-input"
                    />
                </div>
                <div class="w-full md:w-52">
                    <select v-model="bidang" class="field-input">
                        <option value="">Semua Bidang</option>
                        <option value="Aplikasi dan Layanan E-Government">Aplikasi E-Government</option>
                        <option value="Infrastruktur Jaringan dan Server">Infrastruktur Jaringan</option>
                        <option value="Sekretariat dan Tata Usaha">Sekretariat</option>
                        <option value="Diseminasi Informasi Publik">Diseminasi Informasi</option>
                        <option value="Pengelolaan Data dan Statistik">Data dan Statistik</option>
                        <option value="Hubungan Masyarakat dan Media">Hubungan Masyarakat</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Bidang List -->
        <div class="grid gap-4">
            <div v-for="item in filteredBidang" :key="item.id" class="glass-card flex flex-col gap-4 p-5 transition hover:border-forest-500/40 hover:shadow-md group sm:flex-row sm:items-center sm:justify-between">
                <div class="flex min-w-0 items-center gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700 group-hover:bg-forest-600 group-hover:text-white transition">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                            <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="truncate font-display text-base font-bold text-ink-900 group-hover:text-forest-700 transition">
                            {{ item.nama }}
                        </h3>
                        <p class="mt-0.5 truncate text-sm text-ink-500">{{ item.instansi }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    <span class="badge badge-info font-medium">
                        Kuota {{ item.terisi_total }}/{{ item.kuota_total }}
                    </span>
                    <span :class="statusBadge(item.status)" class="badge">
                        {{ statusLabel(item.status) }}
                    </span>
                    <Link :href="route('admin.bidang.show', item.id)" class="btn-secondary px-3 py-1.5 text-xs">
                        Detail
                    </Link>
                    <Link :href="route('admin.bidang.edit', item.id)" class="btn-secondary px-3 py-1.5 text-xs">
                        Edit
                    </Link>
                    <button @click="handleDelete(item)" class="btn-danger px-3 py-1.5 text-xs">
                        Hapus
                    </button>
                </div>
            </div>

            <div v-if="filteredBidang.length === 0" class="glass-panel p-12 text-center text-ink-500">
                Tidak ada bidang yang ditemukan.
            </div>
        </div>
    </AppLayout>
</template>