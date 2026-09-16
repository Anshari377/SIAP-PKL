<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    divisions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    nim: '',
    school: '',
    major: '',
    phone: '',
    division_id: '',
    start_date: '',
    end_date: '',
});

const submitForm = () => form.post(route('admin.peserta.walk-in.store'));
</script>

<template>
    <Head title="Registrasi Walk-in" />
    <AdminLayout title="Registrasi Peserta Walk-in">
        <div class="mx-auto max-w-3xl">
            <div class="glass-panel p-6 sm:p-8">
                <div class="mb-8">
                    <h2 class="font-display text-2xl font-bold text-ink-900">Registrasi Peserta Walk-in</h2>
                    <p class="mt-1 text-sm text-ink-500">Catat peserta yang mendaftar langsung di instansi dan masukkan ke daftar peserta aktif.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="name">Nama Lengkap</label>
                            <input id="name" v-model="form.name" type="text" required class="field-input" />
                            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="nim">NIM/NIS</label>
                            <input id="nim" v-model="form.nim" type="text" class="field-input" />
                            <p v-if="form.errors.nim" class="field-error">{{ form.errors.nim }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="phone">Nomor Telepon</label>
                            <input id="phone" v-model="form.phone" type="tel" required class="field-input" />
                            <p v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="school">Asal Sekolah/Instansi</label>
                            <input id="school" v-model="form.school" type="text" required class="field-input" />
                            <p v-if="form.errors.school" class="field-error">{{ form.errors.school }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="major">Jurusan</label>
                            <input id="major" v-model="form.major" type="text" required class="field-input" />
                            <p v-if="form.errors.major" class="field-error">{{ form.errors.major }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="division_id">Bidang PKL</label>
                            <select id="division_id" v-model="form.division_id" required class="field-input">
                                <option value="" disabled>Pilih bidang</option>
                                <option v-for="division in divisions" :key="division.id" :value="division.id">
                                    {{ division.nama }} (kuota {{ division.quota }})
                                </option>
                            </select>
                            <p v-if="form.errors.division_id" class="field-error">{{ form.errors.division_id }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="start_date">Tanggal Mulai</label>
                            <input id="start_date" v-model="form.start_date" type="date" required class="field-input" />
                            <p v-if="form.errors.start_date" class="field-error">{{ form.errors.start_date }}</p>
                        </div>
                        <div>
                            <label class="field-label" for="end_date">Tanggal Selesai</label>
                            <input id="end_date" v-model="form.end_date" type="date" required class="field-input" />
                            <p v-if="form.errors.end_date" class="field-error">{{ form.errors.end_date }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-ink-300/30 pt-4">
                        <Link :href="route('admin.peserta.index')" class="btn-secondary">Batal</Link>
                        <button type="submit" class="btn-primary disabled:cursor-not-allowed disabled:opacity-50" :disabled="form.processing">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Registrasi' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>