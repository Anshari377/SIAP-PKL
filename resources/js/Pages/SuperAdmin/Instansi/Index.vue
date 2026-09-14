<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';

const search = ref('');
const tipeFilter = ref('');

const instansiList = ref([
    { id: 1, nama: 'Dinas Komunikasi dan Informatika Kaltim', tipe: 'pemerintah', alamat: 'Jl. Kesuma Bangsa No. 12, Samarinda', email: 'admin@dkominfo.kaltimprov.go.id', jumlah_bidang_pkl: 6, jumlah_admin: 3 },
    { id: 2, nama: 'Dinas Pendidikan dan Kebudayaan Kaltim', tipe: 'pemerintah', alamat: 'Jl. Bhayangkara No. 9, Samarinda', email: 'disdik@kaltimprov.go.id', jumlah_bidang_pkl: 4, jumlah_admin: 2 },
    { id: 3, nama: 'RSUD Abdul Wahab Sjahranie', tipe: 'pemerintah', alamat: 'Jl. Palaran Ring Road I, Samarinda', email: 'humas@rsudaws.co.id', jumlah_bidang_pkl: 5, jumlah_admin: 4 },
    { id: 4, nama: 'Bankaltimtara', tipe: 'swasta', alamat: 'Jl. Jend. Sudirman No. 20, Samarinda', email: 'cs@bankaltimtara.co.id', jumlah_bidang_pkl: 3, jumlah_admin: 2 },
    { id: 5, nama: 'PT Pegadaian Cabang Samarinda', tipe: 'swasta', alamat: 'Jl. Pangeran Suriansyah No. 5, Samarinda', email: 'samarinda@pegadaian.co.id', jumlah_bidang_pkl: 2, jumlah_admin: 1 },
    { id: 6, nama: 'Dinas Kesehatan Kaltim', tipe: 'pemerintah', alamat: 'Jl. Awang Long No. 10, Samarinda', email: 'dinkes@kaltimprov.go.id', jumlah_bidang_pkl: 4, jumlah_admin: 2 },
    { id: 7, nama: 'PT Telkom Indonesia Witel Samarinda', tipe: 'swasta', alamat: 'Jl. Dr. Sutomo No. 3, Samarinda', email: 'hcsam@telkom.co.id', jumlah_bidang_pkl: 3, jumlah_admin: 3 },
    { id: 8, nama: 'Dinas Pemuda dan Olahraga Kaltim', tipe: 'pemerintah', alamat: 'Jl. RE Martadinata No. 7, Samarinda', email: 'dispora@kaltimprov.go.id', jumlah_bidang_pkl: 2, jumlah_admin: 1 },
]);

const filteredInstansi = computed(() => {
    return instansiList.value.filter((item) => {
        const matchSearch = !search.value || item.nama.toLowerCase().includes(search.value.toLowerCase());
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

const showModal = ref(false);
const form = reactive({
    nama: '',
    tipe: 'pemerintah',
    alamat: '',
    email: '',
});

const showSuccess = ref(false);

const openModal = () => {
    form.nama = '';
    form.tipe = 'pemerintah';
    form.alamat = '';
    form.email = '';
    showSuccess.value = false;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitForm = () => {
    if (!form.nama || !form.alamat || !form.email) return;
    instansiList.value.push({
        id: instansiList.value.length + 1,
        nama: form.nama,
        tipe: form.tipe,
        alamat: form.alamat,
        email: form.email,
        jumlah_bidang_pkl: 0,
        jumlah_admin: 0,
    });
    showModal.value = false;
    showSuccess.value = true;
    setTimeout(() => { showSuccess.value = false; }, 3000);
};
</script>

<template>
    <Head title="Manajemen Instansi" />
    <SuperAdminLayout title="Manajemen Instansi">
        <!-- Success Alert -->
        <div v-if="showSuccess" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
            Instansi baru berhasil ditambahkan! (Demo — data belum tersimpan)
        </div>

        <!-- Header Action -->
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-xl font-bold text-ink-900">Daftar Instansi PKL</h2>
                <p class="mt-1 text-sm text-ink-500">Kelola seluruh instansi mitra PKL yang terdaftar di sistem.</p>
            </div>
            <button @click="openModal" class="btn-primary w-full sm:w-auto shrink-0">
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
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Nama Instansi</th>
                            <th class="px-4 py-3">Tipe</th>
                            <th class="px-4 py-3">Alamat</th>
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
                            <td class="px-4 py-4 text-ink-700">{{ item.alamat }}</td>
                            <td class="px-4 py-4 text-ink-500">{{ item.email }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-ink-900">{{ item.jumlah_bidang_pkl }}</td>
                            <td class="px-4 py-4 text-center font-semibold text-ink-900">{{ item.jumlah_admin }}</td>
                            <td class="px-4 py-4 text-right">
                                <Link :href="route('superadmin.instansi.show', item.id)" class="btn-secondary px-3 py-1.5 text-xs">
                                    Lihat Detail
                                </Link>
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

        <!-- Tambah Instansi Modal -->
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
                    <div class="relative w-full max-w-lg rounded-3xl border border-white/70 bg-white/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="font-display text-xl font-bold text-ink-900">Tambah Instansi Baru</h3>
                                <p class="mt-1 text-sm text-ink-500">Registrasi instansi mitra PKL ke dalam sistem.</p>
                            </div>
                            <button @click="closeModal" class="grid h-8 w-8 place-items-center rounded-full text-ink-500 hover:bg-ink-100 transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-5">
                            <div>
                                <label class="field-label">Nama Instansi</label>
                                <input v-model="form.nama" type="text" required placeholder="Contoh: Dinas Komunikasi dan Informatika Kaltim" class="field-input" />
                            </div>
                            <div>
                                <label class="field-label">Tipe Instansi</label>
                                <select v-model="form.tipe" class="field-input">
                                    <option value="pemerintah">Pemerintah</option>
                                    <option value="swasta">Swasta</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Alamat</label>
                                <input v-model="form.alamat" type="text" required placeholder="Alamat lengkap instansi" class="field-input" />
                            </div>
                            <div>
                                <label class="field-label">Email Kontak</label>
                                <input v-model="form.email" type="email" required placeholder="admin@instansi.co.id" class="field-input" />
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                                <button type="button" @click="closeModal" class="btn-secondary">Batal</button>
                                <button type="submit" class="btn-primary">Simpan Instansi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </SuperAdminLayout>
</template>
