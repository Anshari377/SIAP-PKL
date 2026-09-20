<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    instansiList: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const search = ref('');
const tipeFilter = ref('');

const filteredInstansi = computed(() => {
    return props.instansiList.filter((item) => {
        const matchSearch = !search.value || (item.nama && item.nama.toLowerCase().includes(search.value.toLowerCase()));
        const matchTipe = !tipeFilter.value || item.tipe === tipeFilter.value;
        return matchSearch && matchTipe;
    });
});

const tipeBadge = (tipe) => {
    return tipe === 'pemerintah' ? 'badge-info' : 'badge-warning';
};

const tipeLabel = (tipe) => {
    return tipe === 'pemerintah' ? 'Pemerintah' : 'Swasta';
};

// Create / Edit Modal state
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    nama: '',
    tipe: 'pemerintah',
    alamat: '',
    maps_link: '',
    email: '',
    deskripsi: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.clearErrors();
    form.nama = item.nama || '';
    form.tipe = item.tipe || 'pemerintah';
    form.alamat = item.alamat === '-' ? '' : item.alamat || '';
    form.maps_link = item.maps_link || '';
    form.email = item.email === '-' ? '' : item.email || '';
    form.deskripsi = item.deskripsi === '-' ? '' : item.deskripsi || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (isEditing.value && editingId.value) {
        form.put(route('superadmin.instansi.update', editingId.value), {
            onSuccess: () => {
                closeModal();
                form.reset();
            },
        });
    } else {
        form.post(route('superadmin.instansi.store'), {
            onSuccess: () => {
                closeModal();
                form.reset();
            },
        });
    }
};

const confirmDelete = (item) => {
    if (confirm(`Yakin ingin menghapus instansi "${item.nama}"? Semua divisi terkait akan dilepas keterkaitannya.`)) {
        router.delete(route('superadmin.instansi.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Manajemen Instansi" />
    <SuperAdminLayout title="Manajemen Instansi">
        <!-- Success Alert -->
        <div v-if="page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm flex items-center justify-between">
            <span>{{ page.props.flash.success }}</span>
        </div>

        <!-- Header Action -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-xl font-bold text-ink-900">Daftar Instansi PKL</h2>
                <p class="mt-1 text-sm text-ink-500">Kelola seluruh instansi mitra PKL, alamat, dan tautan peta lokasi.</p>
            </div>
            <button @click="openCreateModal" class="btn-primary w-full sm:w-auto shrink-0">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                + Tambah Instansi Baru
            </button>
        </div>

        <!-- Search & Filter Bar -->
        <div class="glass-panel mb-6 p-4">
            <form @submit.prevent class="flex flex-col gap-3 md:flex-row md:items-center">
                <div class="flex-1">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama instansi..."
                        class="field-input"
                    />
                </div>
                <div class="w-full md:w-52">
                    <select v-model="tipeFilter" class="field-input">
                        <option value="">Semua Tipe</option>
                        <option value="pemerintah">Pemerintah</option>
                        <option value="swasta">Swasta</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Instansi Table -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Nama Instansi</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Alamat & Tautan Peta</th>
                            <th class="px-4 py-3">Email Kontak</th>
                            <th class="px-4 py-3 text-center">Bidang PKL</th>
                            <th class="px-4 py-3 text-center">Admin</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="(item, index) in filteredInstansi" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500">{{ index + 1 }}</td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-forest-600/10 text-forest-700">
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01"/>
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-ink-900">{{ item.nama }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span :class="tipeBadge(item.tipe)" class="badge">
                                    {{ tipeLabel(item.tipe) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-ink-700 max-w-xs truncate">{{ item.alamat }}</div>
                                <a
                                    v-if="item.maps_url"
                                    :href="item.maps_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-forest-700 hover:text-forest-900 hover:underline"
                                >
                                    <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    Buka Lokasi Peta
                                    <svg viewBox="0 0 24 24" class="h-3 w-3 shrink-0 opacity-70" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                    </svg>
                                </a>
                            </td>
                            <td class="px-4 py-4 text-ink-500">{{ item.email }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-ink-900">{{ item.jumlah_bidang_pkl }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-ink-900">{{ item.jumlah_admin }}</td>
                            <td class="px-4 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="route('superadmin.instansi.show', item.id)" class="btn-secondary px-2.5 py-1 text-xs" title="Lihat Detail">
                                        Detail
                                    </Link>
                                    <button @click="openEditModal(item)" class="rounded-lg border border-forest-300 bg-forest-50 px-2.5 py-1 text-xs font-semibold text-forest-800 hover:bg-forest-100 transition" title="Edit Instansi">
                                        Ubah
                                    </button>
                                    <button @click="confirmDelete(item)" class="rounded-lg border border-rose-300 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition" title="Hapus Instansi">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="filteredInstansi.length === 0">
                            <td colspan="8" class="px-4 py-10 text-center text-ink-500">
                                Tidak ada instansi yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Form (Tambah / Edit Instansi) -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-forest-950/50 backdrop-blur-sm" @click="closeModal" />
                    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-white/70 bg-white/95 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink-900">
                                    {{ isEditing ? 'Ubah Data Instansi' : 'Tambah Instansi Baru' }}
                                </h3>
                                <p class="mt-1 text-sm text-ink-500">
                                    {{ isEditing ? 'Perbarui informasi instansi mitra PKL.' : 'Registrasi instansi mitra PKL ke dalam sistem.' }}
                                </p>
                            </div>
                            <button @click="closeModal" class="grid h-8 w-8 place-items-center rounded-full text-ink-500 hover:bg-ink-100 transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="field-label">Nama Instansi</label>
                                <input v-model="form.nama" type="text" required placeholder="Contoh: Dinas Komunikasi dan Informatika Kaltim" class="field-input" />
                                <div v-if="form.errors.nama" class="mt-1 text-xs text-rose-500">{{ form.errors.nama }}</div>
                            </div>

                            <div>
                                <label class="field-label">Tipe Instansi</label>
                                <select v-model="form.tipe" class="field-input">
                                    <option value="pemerintah">Pemerintah</option>
                                    <option value="swasta">Swasta</option>
                                </select>
                                <div v-if="form.errors.tipe" class="mt-1 text-xs text-rose-500">{{ form.errors.tipe }}</div>
                            </div>

                            <div>
                                <label class="field-label">Alamat Lengkap</label>
                                <input v-model="form.alamat" type="text" placeholder="Jl. Kesuma Bangsa No. 12, Samarinda" class="field-input" />
                                <div v-if="form.errors.alamat" class="mt-1 text-xs text-rose-500">{{ form.errors.alamat }}</div>
                            </div>

                            <div>
                                <label class="field-label">Tautan Peta (Google Maps URL)</label>
                                <input v-model="form.maps_link" type="url" placeholder="https://maps.google.com/?q=..." class="field-input" />
                                <p class="mt-1 text-[11px] text-ink-400">Masukkan URL lengkap Google Maps lokasi instansi.</p>
                                <div v-if="form.errors.maps_link" class="mt-1 text-xs text-rose-500">{{ form.errors.maps_link }}</div>
                            </div>

                            <div>
                                <label class="field-label">Email Kontak</label>
                                <input v-model="form.email" type="email" placeholder="admin@instansi.go.id" class="field-input" />
                                <div v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="field-label">Deskripsi Ringkas</label>
                                <textarea v-model="form.deskripsi" rows="3" placeholder="Profil/deskripsi singkat instansi..." class="field-input"></textarea>
                                <div v-if="form.errors.deskripsi" class="mt-1 text-xs text-rose-500">{{ form.errors.deskripsi }}</div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                                <button type="submit" :disabled="form.processing" class="btn-primary">
                                    {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan' : 'Simpan Instansi') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>
