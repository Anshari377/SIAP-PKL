<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    instansi: {
        type: Object,
        required: true,
    },
    bidangPkl: {
        type: Array,
        default: () => [],
    },
    adminList: {
        type: Array,
        default: () => [],
    },
});

const tipeBadge = () => {
    return props.instansi.tipe === 'pemerintah' ? 'badge-info' : 'badge-warning';
};

const tipeLabel = () => {
    return props.instansi.tipe === 'pemerintah' ? 'Pemerintah' : 'Swasta';
};

const statusBadge = (status) => {
    return status === 'aktif' ? 'badge-success' : status === 'penuh' ? 'badge-warning' : 'badge-danger';
};

const statusLabel = (status) => {
    return status === 'aktif' ? 'Aktif' : status === 'penuh' ? 'Penuh' : 'Nonaktif';
};

const getInitials = (nama) => {
    if (!nama) return 'AD';
    return nama.split(' ').map((w) => w[0]).slice(0, 2).join('');
};

// Edit Modal state
const showEditModal = ref(false);
const form = useForm({
    nama: props.instansi.nama || '',
    tipe: props.instansi.tipe || 'pemerintah',
    alamat: props.instansi.alamat === '-' ? '' : props.instansi.alamat || '',
    maps_link: props.instansi.maps_link || '',
    email: props.instansi.email === '-' ? '' : props.instansi.email || '',
    deskripsi: props.instansi.deskripsi === '-' ? '' : props.instansi.deskripsi || '',
});

const openEditModal = () => {
    form.clearErrors();
    form.nama = props.instansi.nama || '';
    form.tipe = props.instansi.tipe || 'pemerintah';
    form.alamat = props.instansi.alamat === '-' ? '' : props.instansi.alamat || '';
    form.maps_link = props.instansi.maps_link || '';
    form.email = props.instansi.email === '-' ? '' : props.instansi.email || '';
    form.deskripsi = props.instansi.deskripsi === '-' ? '' : props.instansi.deskripsi || '';
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const submitEdit = () => {
    form.put(route('superadmin.instansi.update', props.instansi.id), {
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const confirmDelete = () => {
    if (confirm(`Yakin ingin menghapus instansi "${props.instansi.nama}"? Semua divisi terkait akan dilepas keterkaitannya.`)) {
        router.delete(route('superadmin.instansi.destroy', props.instansi.id));
    }
};
</script>

<template>
    <Head :title="`Detail Instansi - ${instansi.nama}`" />
    <SuperAdminLayout title="Detail Instansi">
        <div class="mb-4">
            <Link :href="route('superadmin.instansi.index')" class="inline-flex items-center gap-1 text-sm font-medium text-forest-700 hover:underline">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali ke Manajemen Instansi
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Info Lengkap -->
                <div class="glass-panel p-6 sm:p-8">
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-4">
                            <div class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-forest-600/10 text-forest-700">
                                <svg viewBox="0 0 24 24" class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M16 6h.01M12 6h.01M12 10h.01M12 14h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-display text-xl sm:text-2xl font-bold text-ink-900">{{ instansi.nama }}</h2>
                                <p class="mt-1 text-sm text-ink-500">Terdaftar sejak {{ instansi.created_at }}</p>
                            </div>
                        </div>
                        <span :class="tipeBadge()" class="badge w-fit shrink-0">
                            {{ tipeLabel() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Alamat</p>
                            <p class="mt-1 font-medium text-ink-900">{{ instansi.alamat }}</p>
                            <a
                                v-if="instansi.maps_url"
                                :href="instansi.maps_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 mt-2 rounded-lg border border-forest-300 bg-forest-50 px-3 py-1.5 text-xs font-semibold text-forest-800 hover:bg-forest-100 transition shadow-sm"
                            >
                                <svg viewBox="0 0 24 24" class="h-4 w-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                                </svg>
                                Lihat Lokasi di Google Maps
                                <svg viewBox="0 0 24 24" class="h-3 w-3 shrink-0 opacity-70" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Email Kontak</p>
                            <p class="mt-1 text-ink-800">{{ instansi.email }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Deskripsi</p>
                            <p class="mt-1 text-sm text-ink-800 leading-relaxed">{{ instansi.deskripsi }}</p>
                        </div>
                    </div>
                </div>

                <!-- List Bidang PKL -->
                <div class="glass-panel p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-display text-base font-bold text-ink-900">Bidang PKL</h3>
                        <span class="badge badge-info">{{ bidangPkl.length }} Bidang</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Bidang</th>
                                    <th class="px-4 py-3">Kuota</th>
                                    <th class="px-4 py-3">Terisi</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-ink-300/20">
                                <tr v-for="(bidang, index) in bidangPkl" :key="bidang.id" class="transition hover:bg-forest-50/60">
                                    <td class="px-4 py-3.5 text-ink-500">{{ index + 1 }}</td>
                                    <td class="px-4 py-3.5 font-semibold text-ink-900">{{ bidang.nama }}</td>
                                    <td class="px-4 py-3.5 text-ink-700">{{ bidang.kuota }}</td>
                                    <td class="px-4 py-3.5 text-ink-700">{{ bidang.terisi }}</td>
                                    <td class="px-4 py-3.5">
                                        <span :class="statusBadge(bidang.status)" class="badge">
                                            {{ statusLabel(bidang.status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="bidangPkl.length === 0">
                                    <td colspan="5" class="px-4 py-6 text-center text-ink-500">
                                        Belum ada bidang PKL untuk instansi ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="space-y-6">
                <!-- Aksi -->
                <div class="glass-card p-6 space-y-4 h-fit">
                    <h3 class="font-display text-base font-bold text-ink-900 border-b border-ink-300/30 pb-3">
                        Aksi Instansi
                    </h3>

                    <button @click="openEditModal" class="w-full rounded-xl border border-forest-300 bg-forest-50 px-4 py-2.5 text-sm font-semibold text-forest-800 hover:bg-forest-100 transition shadow-sm flex items-center justify-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                        Ubah Data Instansi
                    </button>

                    <Link :href="route('superadmin.undangan.index')" class="btn-primary w-full text-center text-sm flex items-center justify-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" />
                        </svg>
                        Undang Admin Instansi
                    </Link>

                    <button @click="confirmDelete" class="w-full rounded-xl border border-rose-300 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition flex items-center justify-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Hapus Instansi
                    </button>
                </div>

                <!-- Admin Terdaftar -->
                <div class="glass-card p-6">
                    <div class="mb-4 border-b border-ink-300/30 pb-3 flex items-center justify-between">
                        <h3 class="font-display text-base font-bold text-ink-900">Admin Instansi</h3>
                        <span class="badge badge-info">{{ adminList.length }} Admin</span>
                    </div>
                    <ul class="space-y-4">
                        <li v-for="admin in adminList" :key="admin.id" class="rounded-xl border border-ink-300/40 bg-white/50 p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-2">
                                <div class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-forest-700 text-xs font-semibold text-white">
                                    {{ getInitials(admin.nama) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink-900">{{ admin.nama }}</p>
                                    <p class="truncate text-xs text-ink-500">{{ admin.email }}</p>
                                </div>
                                <span :class="admin.status ? 'badge-success' : 'badge-danger'" class="badge text-[10px]">
                                    {{ admin.status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                        </li>
                        <li v-if="adminList.length === 0" class="text-xs text-ink-500 text-center py-4">
                            Belum ada admin terdaftar untuk instansi ini.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Edit Instansi Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div class="absolute inset-0 bg-forest-950/50 backdrop-blur-sm" @click="closeEditModal" />
                    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-3xl border border-white/70 bg-white/95 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink-900">Ubah Data Instansi</h3>
                                <p class="mt-1 text-sm text-ink-500">Perbarui informasi instansi mitra PKL.</p>
                            </div>
                            <button @click="closeEditModal" class="grid h-8 w-8 place-items-center rounded-full text-ink-500 hover:bg-ink-100 transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitEdit" class="space-y-4">
                            <div>
                                <label class="field-label">Nama Instansi</label>
                                <input v-model="form.nama" type="text" required class="field-input" />
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
                                <input v-model="form.alamat" type="text" class="field-input" />
                                <div v-if="form.errors.alamat" class="mt-1 text-xs text-rose-500">{{ form.errors.alamat }}</div>
                            </div>

                            <div>
                                <label class="field-label">Tautan Peta (Google Maps URL)</label>
                                <input v-model="form.maps_link" type="url" placeholder="https://maps.google.com/?q=..." class="field-input" />
                                <div v-if="form.errors.maps_link" class="mt-1 text-xs text-rose-500">{{ form.errors.maps_link }}</div>
                            </div>

                            <div>
                                <label class="field-label">Email Kontak</label>
                                <input v-model="form.email" type="email" class="field-input" />
                                <div v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</div>
                            </div>

                            <div>
                                <label class="field-label">Deskripsi Ringkas</label>
                                <textarea v-model="form.deskripsi" rows="3" class="field-input"></textarea>
                                <div v-if="form.errors.deskripsi" class="mt-1 text-xs text-rose-500">{{ form.errors.deskripsi }}</div>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                                <button type="button" @click="closeEditModal" class="btn-secondary">Batal</button>
                                <button type="submit" :disabled="form.processing" class="btn-primary">
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>
