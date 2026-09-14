<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const bidang = ref({
    id: 1,
    nama: 'Aplikasi dan Layanan E-Government',
    instansi: 'Diskominfo Kaltim',
    deskripsi: 'Bidang ini menangani pengembangan dan pemeliharaan aplikasi layanan pemerintah berbasis elektronik (e-Government) untuk memudahkan akses layanan publik di Kalimantan Timur.',
    kualifikasi: 'Mahasiswa/Siswa jurusan Teknik Informatika, Sistem Informasi, atau bidang terkait. Menguasai dasar pemrograman web (HTML, CSS, JavaScript). Berhasil menyelesaikan proses seleksi.',
    kuota_total: 8,
    terisi_total: 5,
    status: 'aktif',
    created_at: '2026-08-01',
    posisi: [
        { id: 1, nama: 'Web Developer', kuota: 3, terisi: 2, jurusan: ['Teknik Informatika', 'Sistem Informasi'] },
        { id: 2, nama: 'Mobile App Developer', kuota: 2, terisi: 1, jurusan: ['Teknik Informatika'] },
        { id: 3, nama: 'UI/UX Designer', kuota: 1, terisi: 1, jurusan: ['Desain Komunikasi Visual', 'Teknik Informatika'] },
        { id: 4, nama: 'Database Administrator', kuota: 2, terisi: 1, jurusan: ['Teknik Informatika', 'Sistem Informasi'] },
    ],
});

const handleToggleStatus = () => {
    bidang.value.status = bidang.value.status === 'aktif' ? 'nonaktif' : 'aktif';
};
</script>

<template>
    <Head title="Detail Bidang" />
    <AppLayout title="Detail Bidang PKL">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Detail Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Info -->
                <div class="glass-panel p-6 sm:p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h2 class="font-display text-2xl font-bold text-ink-900">{{ bidang.nama }}</h2>
                            <p class="mt-1 text-sm text-ink-500">{{ bidang.instansi }} · Dibuat {{ bidang.created_at }}</p>
                        </div>
                        <span
                            :class="bidang.status === 'aktif' ? 'badge-success' : 'badge-danger'"
                            class="badge"
                        >
                            {{ bidang.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-2">Deskripsi Bidang</h3>
                            <p class="text-sm text-ink-800 leading-relaxed">{{ bidang.deskripsi }}</p>
                        </div>
                        <div class="border-t border-ink-300/30 pt-4">
                            <h3 class="text-xs font-medium text-ink-500 uppercase tracking-wider mb-2">Kualifikasi</h3>
                            <p class="text-sm text-ink-800 leading-relaxed">{{ bidang.kualifikasi }}</p>
                        </div>
                    </div>
                </div>

                <!-- Posisi Table -->
                
            </div>

            <!-- Right: Sidebar Card -->
            <div class="space-y-6">
                <div class="glass-card p-6 space-y-4 h-fit">
                    <h3 class="font-display text-base font-bold text-ink-900 border-b border-ink-300/30 pb-3">
                        Ringkasan Kuota
                    </h3>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Total Kuota</p>
                        <p class="mt-1 font-display text-2xl font-bold text-ink-900">{{ bidang.kuota_total }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Terisi</p>
                        <p class="mt-1 font-display text-2xl font-bold text-forest-700">{{ bidang.terisi_total }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Sisa Kuota</p>
                        <p class="mt-1 font-display text-2xl font-bold" :class="bidang.kuota_total - bidang.terisi_total > 0 ? 'text-status-success' : 'text-status-danger'">
                            {{ bidang.kuota_total - bidang.terisi_total }}
                        </p>
                    </div>
                    <div class="pt-3 border-t border-ink-300/30 space-y-2">
                        <Link :href="route('admin.bidang.edit', bidang.id)" class="btn-primary w-full text-center text-sm">
                            Edit Bidang
                        </Link>
                        <button @click="handleToggleStatus" class="btn-secondary w-full text-sm">
                            {{ bidang.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} Bidang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>