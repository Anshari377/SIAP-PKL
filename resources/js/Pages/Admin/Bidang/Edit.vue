<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ bidang: { type: Object, required: true } });

const form = useForm({
    nama: props.bidang.nama ?? '',
    kategori: props.bidang.kategori ?? '',
    deskripsi: props.bidang.deskripsi ?? '',
    kuota_total: props.bidang.quota ?? '',
    jurusan: (props.bidang.jurusan ?? []).join(', '),
    positions: (props.bidang.positions ?? []).map((p) => ({
        id: p.id,
        nama: p.nama ?? '',
    })),
});

const addPosition = () => {
    form.positions.push({ id: null, nama: '' });
};

const removePosition = (index) => {
    form.positions.splice(index, 1);
};

const submitForm = () => {
    form.put(route('admin.bidang.update', props.bidang.id));
};
</script>

<template>
    <Head title="Edit Bidang" />
    <AdminLayout title="Edit Bidang PKL">
        <div class="mx-auto max-w-3xl">
            <div class="glass-panel p-6 sm:p-8">
                <div class="mb-8">
                    <h2 class="font-display text-2xl font-bold text-ink-900">Edit Bidang</h2>
                    <p class="mt-1 text-sm text-ink-500">Perbarui informasi bidang praktik kerja lapangan.</p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    <!-- Nama Bidang -->
                    <div>
                        <label class="field-label" for="nama">Nama Bidang</label>
                        <input id="nama" v-model="form.nama" type="text" required class="field-input" />
                        <p v-if="form.errors.nama" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.nama }}</p>
                    </div>

                    <!-- Kategori -->
                    <div>
                        <label class="field-label" for="kategori">Kategori <span class="text-ink-400">(opsional)</span></label>
                        <input id="kategori" v-model="form.kategori" type="text" class="field-input" />
                        <p v-if="form.errors.kategori" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.kategori }}</p>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="field-label" for="deskripsi">Deskripsi</label>
                        <textarea id="deskripsi" v-model="form.deskripsi" rows="4" required class="field-input" />
                        <p v-if="form.errors.deskripsi" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.deskripsi }}</p>
                    </div>

                    <!-- Kuota Total -->
                    <div>
                        <label class="field-label" for="kuota_total">Kuota Total</label>
                        <input id="kuota_total" v-model="form.kuota_total" type="number" min="1" required class="field-input max-w-xs" />
                        <p v-if="form.errors.kuota_total" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.kuota_total }}</p>
                    </div>

                    <!-- Jurusan yang Dicari -->
                    <div>
                        <label class="field-label" for="jurusan">Jurusan yang Dicari <span class="text-ink-400">(opsional)</span></label>
                        <input id="jurusan" v-model="form.jurusan" type="text" placeholder="Pisahkan dengan koma, contoh: RPL, TKJ, Informatika" class="field-input" />
                        <p v-if="form.errors.jurusan" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.jurusan }}</p>
                    </div>

                    <!-- Sub-Posisi PKL -->
                    <div class="border-t border-ink-300/30 pt-6">
                        <div class="mb-4 flex items-center justify-between">
                            <div>
                                <h3 class="font-display text-base font-bold text-ink-900">Sub-Posisi PKL</h3>
                                <p class="mt-0.5 text-xs text-ink-500">
                                    Posisi yang sedang memiliki pengajuan aktif tidak dapat dihapus.
                                </p>
                            </div>
                            <button type="button" @click="addPosition" class="btn-secondary shrink-0 text-xs px-3 py-1.5">
                                <svg viewBox="0 0 24 24" class="h-3.5 w-3.5 mr-1 inline" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Tambah Posisi
                            </button>
                        </div>

                        <div v-if="form.positions.length === 0" class="rounded-xl border border-dashed border-ink-300/60 py-8 text-center text-sm text-ink-400">
                            Belum ada posisi. Klik "Tambah Posisi" untuk menambahkan, atau biarkan kosong untuk mempertahankan posisi yang ada.
                        </div>

                        <div
                            v-for="(pos, index) in form.positions"
                            :key="pos.id ?? `new-${index}`"
                            class="mb-4 rounded-xl border border-ink-300/40 bg-white/60 p-5"
                        >
                            <div class="mb-4 flex items-center justify-between">
                                <p class="text-sm font-bold text-ink-900">
                                    Posisi {{ index + 1 }}
                                    <span v-if="pos.id" class="ml-1.5 text-xs font-normal text-ink-400">(existing)</span>
                                </p>
                                <button type="button" @click="removePosition(index)" class="inline-flex items-center gap-1 text-xs font-semibold text-status-danger hover:underline">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                    </svg>
                                    Hapus
                                </button>
                            </div>
                            <div>
                                <label class="field-label" :for="`pos_nama_${index}`">Nama Posisi <span class="text-red-600">*</span></label>
                                <input :id="`pos_nama_${index}`" v-model="pos.nama" type="text" required placeholder="Contoh: Web Developer" class="field-input" />
                                <p v-if="form.errors[`positions.${index}.nama`]" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors[`positions.${index}.nama`] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-ink-300/30">
                        <Link :href="route('admin.bidang.index')" class="btn-secondary">Batal</Link>
                        <button
                            type="submit"
                            class="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Menyimpan...' : 'Perbarui Bidang' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>