<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    nama: '',
    kategori: '',
    deskripsi: '',
    kuota_total: '',
    jurusan: '',
});

const submitForm = () => {
    form.post(route('admin.bidang.store'));
};
</script>

<template>
    <Head title="Buat Bidang" />
    <AdminLayout title="Buat Bidang PKL">
        <div class="mx-auto max-w-3xl">
            <div class="glass-panel p-6 sm:p-8">
                <div class="mb-8">
                    <h2 class="font-display text-2xl font-bold text-ink-900">Buat Bidang Baru</h2>
                    <p class="mt-1 text-sm text-ink-500">Isi informasi bidang praktik kerja lapangan yang akan dibuka.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Nama Bidang -->
                    <div>
                        <label class="field-label" for="nama">Nama Bidang</label>
                        <input id="nama" v-model="form.nama" type="text" required placeholder="Contoh: Aplikasi dan Layanan E-Government" class="field-input" />
                        <p v-if="form.errors.nama" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.nama }}</p>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="field-label" for="kategori">Kategori <span class="text-ink-400">(opsional)</span></label>
                        <input id="kategori" v-model="form.kategori" type="text" placeholder="Contoh: Aplikasi dan Layanan E-Government" class="field-input" />
                        <p v-if="form.errors.kategori" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.kategori }}</p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="field-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" v-model="form.deskripsi" rows="4" required placeholder="Jelaskan deskripsi bidang ini..." class="field-input" />
                        <p v-if="form.errors.deskripsi" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.deskripsi }}</p>
                    </div>

                    <!-- Kuota Total -->
                    <div>
                        <label class="field-label" for="kuota_total">Kuota Total</label>
                        <input id="kuota_total" v-model="form.kuota_total" type="number" min="1" required placeholder="Jumlah kuota" class="field-input max-w-xs" />
                        <p v-if="form.errors.kuota_total" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.kuota_total }}</p>
                    </div>

                    <!-- Jurusan yang Dicari -->
                    <div>
                        <label class="field-label" for="jurusan">Jurusan yang Dicari <span class="text-ink-400">(opsional)</span></label>
                        <input id="jurusan" v-model="form.jurusan" type="text" placeholder="Pisahkan dengan koma, contoh: RPL, TKJ, Informatika" class="field-input" />
                        <p v-if="form.errors.jurusan" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.jurusan }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                        <Link :href="route('admin.bidang.index')" class="btn-secondary">Batal</Link>
                        <button type="submit" class="btn-primary disabled:cursor-not-allowed disabled:opacity-50" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Bidang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>