<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const form = reactive({
    nama: '',
    instansi: 'Diskominfo Kaltim',
    deskripsi: '',
    kualifikasi: '',
    kuota_total: '',
    status: 'aktif',
    posisi: [{ nama: '', kuota: '', jurusan: '' }],
});

const addPosisi = () => {
    form.posisi.push({ nama: '', kuota: '', jurusan: '' });
};

const removePosisi = (index) => {
    if (form.posisi.length > 1) {
        form.posisi.splice(index, 1);
    }
};

const submitForm = () => {
    useForm(form).post(route('admin.bidang.store'));
};
</script>

<template>
    <Head title="Buat Bidang" />
    <AppLayout title="Buat Bidang PKL">
        <div class="mx-auto max-w-3xl">
            <!-- Success Alert -->
            <div class="glass-panel p-6 sm:p-8">
                <div class="mb-8">
                    <h2 class="font-display text-2xl font-bold text-ink-900">Buat Bidang Baru</h2>
                    <p class="mt-1 text-sm text-ink-500">Isi informasi bidang praktik kerja lapangan yang akan dibuka.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Nama Bidang -->
                    <div>
                        <label class="field-label">Nama Bidang</label>
                        <input v-model="form.nama" type="text" required placeholder="Contoh: Aplikasi dan Layanan E-Government" class="field-input" />
                    </div>

                    <!-- Instansi -->
                    <div>
                        <label class="field-label">Instansi</label>
                        <input v-model="form.instansi" type="text" class="field-input" />
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="field-label">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="4" placeholder="Jelaskan deskripsi bidang ini..." class="field-input" />
                    </div>

                    <!-- Kualifikasi -->
                    <div>
                        <label class="field-label">Kualifikasi</label>
                        <textarea v-model="form.kualifikasi" rows="3" placeholder="Jelaskan kualifikasi yang dibutuhkan..." class="field-input" />
                    </div>

                    <!-- Kuota Total -->
                    <div>
                        <label class="field-label">Kuota Total</label>
                        <input v-model="form.kuota_total" type="number" min="1" required placeholder="Jumlah kuota" class="field-input max-w-xs" />
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="field-label">Status</label>
                        <select v-model="form.status" class="field-input max-w-xs">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <!-- Posisi Section -->
                    <div class="border-t border-ink-300/30 pt-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-display text-base font-bold text-ink-900">Daftar Posisi</h3>
                            <button type="button" @click="addPosisi" class="text-xs font-semibold text-forest-700 hover:underline">
                                + Tambah Posisi
                            </button>
                        </div>

                        <div v-for="(pos, index) in form.posisi" :key="index" class="mb-4 rounded-xl border border-ink-300/40 bg-white/50 p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs font-medium text-ink-500">Posisi {{ index + 1 }}</span>
                                <button v-if="form.posisi.length > 1" type="button" @click="removePosisi(index)" class="text-xs font-medium text-status-danger hover:underline">
                                    Hapus
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <label class="field-label">Nama Posisi</label>
                                    <input v-model="pos.nama" type="text" placeholder="Contoh: Web Developer" class="field-input" />
                                </div>
                                <div>
                                    <label class="field-label">Kuota</label>
                                    <input v-model="pos.kuota" type="number" min="1" placeholder="Jumlah" class="field-input" />
                                </div>
                                <div>
                                    <label class="field-label">Jurusan</label>
                                    <input v-model="pos.jurusan" type="text" placeholder="Pisahkan dengan koma" class="field-input" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                        <Link :href="route('admin.bidang.index')" class="btn-secondary">Batal</Link>
                        <button type="submit" class="btn-primary">Simpan Bidang</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>