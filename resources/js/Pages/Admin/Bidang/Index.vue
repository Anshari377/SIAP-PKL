<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ divisions: { type: Array, default: () => [] } });
const search = ref('');
const bidang = ref('');

const divisionNames = computed(() => [...new Set(props.divisions.map((d) => d.nama))]);

const filteredBidang = computed(() => {
    return props.divisions.filter((item) => {
        const matchSearch = !search.value || item.nama.toLowerCase().includes(search.value.toLowerCase());
        const matchBidang = !bidang.value || item.nama === bidang.value;
        return matchSearch && matchBidang;
    });
});

const statusBadge = (status) => {
    switch (status) {
        case 'tersedia': return 'badge-success';
        case 'menipis': return 'badge-warning';
        case 'penuh':
        case 'hampir-penuh': return 'badge-danger';
        default: return 'badge-info';
    }
};

const statusLabel = (status) => {
    switch (status) {
        case 'tersedia': return 'Slot Tersedia';
        case 'menipis': return 'Kuota Menipis';
        case 'hampir-penuh': return 'Hampir Penuh';
        case 'penuh': return 'Kuota Penuh';
        default: return status;
    }
};

const handleDelete = (item) => {
    if (confirm(`Hapus bidang "${item.nama}"?`)) {
        router.delete(route('admin.bidang.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Kelola Bidang" />
    <AdminLayout title="Kelola Bidang PKL">
        <div class="mx-auto max-w-7xl">
            <!-- Header Action -->
            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-display text-xl font-bold text-ink-900 sm:text-2xl">Daftar Bidang PKL</h2>
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
                            class="field-input w-full"
                        />
                    </div>
                    <div class="w-full md:w-52">
                        <select v-model="bidang" class="field-input w-full">
                            <option value="">Semua Bidang</option>
                            <option v-for="name in divisionNames" :key="name" :value="name">{{ name }}</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Bidang List -->
            <div class="grid gap-4">
                <div
                    v-for="item in filteredBidang"
                    :key="item.id"
                    class="glass-card flex flex-col gap-4 p-4 sm:p-5 transition hover:border-forest-500/40 hover:shadow-md group"
                >
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <!-- Info Left Section -->
                        <div class="flex min-w-0 flex-1 items-center gap-3.5 sm:gap-4">
                            <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700 group-hover:bg-forest-600 group-hover:text-white transition">
                                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3
                                    class="truncate font-display text-base font-bold text-ink-900 group-hover:text-forest-700 transition"
                                    :title="item.nama"
                                >
                                    {{ item.nama }}
                                </h3>
                                <p class="mt-0.5 truncate text-sm text-ink-500" :title="item.instansi">
                                    {{ item.instansi }}
                                </p>
                            </div>
                        </div>

                        <!-- Badges & Action Buttons Right Section -->
                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 shrink-0">
                            <!-- Badges -->
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="badge badge-info font-medium shrink-0 whitespace-nowrap">
                                    Kuota {{ item.terisi_total }}/{{ item.kuota_total }}
                                </span>
                                <span :class="statusBadge(item.status)" class="badge shrink-0 whitespace-nowrap">
                                    {{ statusLabel(item.status) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                                <Link :href="route('admin.bidang.show', item.id)" class="btn-secondary px-3 py-1.5 text-xs whitespace-nowrap shrink-0">
                                    Detail
                                </Link>
                                <Link :href="route('admin.bidang.edit', item.id)" class="btn-secondary px-3 py-1.5 text-xs whitespace-nowrap shrink-0">
                                    Edit
                                </Link>
                                <button @click="handleDelete(item)" class="btn-danger px-3 py-1.5 text-xs whitespace-nowrap shrink-0">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="(item.jurusan ?? []).length" class="flex flex-wrap items-center gap-1.5 border-t border-ink-300/30 pt-3 sm:pt-4">
                        <span v-for="jurusan in item.jurusan" :key="jurusan" class="badge badge-info text-xs">
                            {{ jurusan }}
                        </span>
                    </div>
                </div>

                <div v-if="filteredBidang.length === 0" class="glass-panel p-12 text-center text-ink-500">
                    Tidak ada bidang yang ditemukan.
                </div>
            </div>
        </div>
    </AdminLayout>
</template>