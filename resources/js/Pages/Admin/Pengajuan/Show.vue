<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import { getStatusLabel, getStatusBadgeClass, formatDate, getTimelineSteps } from '@/utils/statusLabel';

const props = defineProps({
    pengajuan: { type: Object, required: true },
});

const pengajuan = computed(() => props.pengajuan);
const ketua = computed(() => pengajuan.value.members?.[0] ?? {});
const anggota = computed(() => pengajuan.value.members ?? []);
const documentName = computed(() => pengajuan.value.document_path?.split('/').pop() ?? 'Berkas belum tersedia');
const steps = computed(() => getTimelineSteps(pengajuan.value));

const showRevisionModal = ref(false);
const revisionError = ref('');
const suratBalasanAlert = ref(null);

const statusForm = useForm({
    status: '',
    surat_balasan: null,
    _method: 'patch',
});

const revisionForm = useForm({
    status: 'revision',
    catatan_revisi: '',
});

const suratBalasanError = computed(() => statusForm.errors.surat_balasan ?? '');
const formError = computed(() => statusForm.errors.status ?? statusForm.error ?? '');

watch([suratBalasanError, formError], ([suratBalasanErr, formErr]) => {
    if ((suratBalasanErr || formErr) && suratBalasanAlert.value) {
        nextTick(() => suratBalasanAlert.value?.scrollIntoView({ behavior: 'smooth', block: 'center' }));
    }
});

const handleApprove = () => {
    if (!statusForm.surat_balasan) {
        statusForm.clearErrors();
        statusForm.setError('surat_balasan', 'Surat balasan wajib diunggah sebelum menerima pengajuan.');
        return;
    }

    statusForm.clearErrors();
    statusForm.status = 'accepted';
    submitStatus('accepted');
};

const handleReject = () => {
    if (!statusForm.surat_balasan) {
        statusForm.clearErrors();
        statusForm.setError('surat_balasan', 'Surat balasan wajib diunggah sebelum menolak pengajuan.');
        return;
    }

    statusForm.clearErrors();
    statusForm.status = 'rejected';
    submitStatus('rejected');
};

const submitStatus = (status) => {
    statusForm.transform((data) => ({ ...data, status }));
    statusForm.post(route('admin.pengajuan.status', pengajuan.value.id), {
        forceFormData: true,
        onSuccess: () => statusForm.reset(),
    });
};

const selectSuratBalasan = (event) => {
    const file = event.target.files?.[0] ?? null;
    statusForm.clearErrors();
    statusForm.surat_balasan = file;
    if (file && file.size > 5 * 1024 * 1024) {
        statusForm.surat_balasan = null;
        statusForm.setError('surat_balasan', 'Ukuran file melebihi batas maksimal 5 MB.');
        event.target.value = '';
    }
};

const submitRevision = () => {
    if (!revisionForm.catatan_revisi.trim()) {
        revisionError.value = 'Catatan revisi wajib diisi.';
        return;
    }
    revisionError.value = '';
    revisionForm.patch(route('admin.pengajuan.status', pengajuan.value.id), {
        onSuccess: () => {
            showRevisionModal.value = false;
            revisionForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Detail Pengajuan" />
    <AdminLayout title="Detail Pengajuan PKL">
        <div class="mb-4">
            <Link :href="route('admin.pengajuan.index')" class="inline-flex items-center gap-1 text-sm font-medium text-forest-700 hover:underline">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                Kembali ke Daftar Pengajuan
            </Link>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Detail Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Revision Note Box if application is revision -->
                <div v-if="pengajuan.status === 'revision' || pengajuan.catatan_revisi" class="rounded-2xl border border-amber-300 bg-amber-50/80 p-6 shadow-sm">
                    <div class="flex items-center gap-2.5 text-amber-800 font-bold mb-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Catatan Revisi dari Admin
                    </div>
                    <p class="text-sm leading-relaxed text-amber-900 bg-white/70 p-3.5 rounded-xl border border-amber-200">
                        {{ pengajuan.catatan_revisi || 'Belum ada catatan detail.' }}
                    </p>
                </div>

                <!-- Applicant Info -->
                <div class="glass-panel p-6 sm:p-8">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="font-display text-2xl font-bold text-ink-900">Informasi Pelamar</h2>
                        <span :class="getStatusBadgeClass(pengajuan.status)" class="badge">
                            {{ getStatusLabel(pengajuan.status) }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Nama Lengkap</p>
                            <p class="mt-1 font-semibold text-ink-900">{{ pengajuan.user?.name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Email</p>
                            <p class="mt-1 text-ink-800">{{ pengajuan.user?.email ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Asal Sekolah/Kampus</p>
                            <p class="mt-1 text-ink-800">{{ ketua.school ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Jurusan</p>
                            <p class="mt-1 text-ink-800">{{ ketua.major ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">NIM</p>
                            <p class="mt-1 text-ink-800">{{ ketua.nim ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">No. Telepon</p>
                            <p class="mt-1 text-ink-800">{{ ketua.phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Position Applied -->
                <div class="glass-panel p-6">
                    <h3 class="mb-4 font-display text-base font-bold text-ink-900">Posisi yang Dilamar</h3>
                    <div class="flex items-center gap-4 rounded-xl border border-ink-300/40 bg-white/50 p-4 shadow-sm">
                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-forest-600/10 text-forest-700">
                            <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-ink-900">{{ pengajuan.division?.nama ?? '-' }}</p>
                            <p class="text-sm text-ink-500">{{ pengajuan.division?.instansi ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Period and members -->
                <div class="glass-panel p-6">
                    <h3 class="mb-4 font-display text-base font-bold text-ink-900">Periode PKL</h3>
                    <p class="text-sm text-ink-800">{{ formatDate(pengajuan.start_date) }} - {{ formatDate(pengajuan.end_date) }}</p>
                    <div v-if="anggota.length" class="mt-5 border-t border-ink-300/30 pt-4">
                        <h3 class="mb-3 font-display text-base font-bold text-ink-900">Anggota Pengajuan</h3>
                        <div class="space-y-2">
                            <div v-for="member in anggota" :key="member.id" class="rounded-lg border border-ink-300/40 bg-white/50 p-3">
                                <p class="font-medium text-ink-900">{{ member.name }}</p>
                                <p class="text-xs text-ink-500">{{ member.major }} · {{ member.school }} · {{ member.phone }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Berkas Upload (satu file gabungan) -->
                <div class="glass-panel p-6">
                    <h3 class="mb-4 font-display text-base font-bold text-ink-900">Berkas yang Diunggah</h3>
                    <div class="flex items-center gap-3 rounded-lg border border-ink-300/40 bg-white/50 p-3">
                        <div class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-forest-600/10 text-forest-700">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-ink-900">{{ documentName }}</p>
                            <p class="text-xs text-ink-500">Dokumen PDF pengajuan PKL</p>
                        </div>
                        <a v-if="pengajuan.document_path" :href="route('admin.pengajuan.document', pengajuan.id)" target="_blank" class="text-xs font-semibold text-forest-700 hover:underline">Lihat</a>
                        <span v-else class="text-xs text-ink-400">Tidak tersedia</span>
                    </div>
                </div>
            </div>

            <!-- Right: Sidebar -->
            <div class="space-y-6">
                <!-- Action Card -->
                <div class="glass-card p-6 space-y-4 h-fit">
                    <h3 class="font-display text-base font-bold text-ink-900 border-b border-ink-300/30 pb-3">
                        Aksi Pengajuan
                    </h3>
                    <p class="text-sm text-ink-500">Tinjau pengajuan ini dan tentukan keputusan.</p>
                    <div class="space-y-2">
                        <template v-if="pengajuan.status === 'pending'">
                            <div>
                                <label class="field-label" for="surat_balasan">Surat Balasan</label>
                                <input id="surat_balasan" type="file" accept=".pdf,.doc,.docx" class="field-input text-xs" :class="{ '!border-red-500 !ring-2 !ring-red-500/25': suratBalasanError }" @change="selectSuratBalasan" :disabled="statusForm.processing" />
                                <p class="mt-1 text-[11px] text-ink-500">PDF, DOC, atau DOCX maksimal 5 MB. Wajib untuk menerima atau menolak.</p>
                                <p v-if="suratBalasanError" class="field-error">{{ suratBalasanError }}</p>
                            </div>
                            <p class="text-[10px] leading-snug text-ink-500">
                                * Surat Balasan wajib diunggah untuk aksi Terima atau Tolak.
                            </p>
                            <div v-if="suratBalasanError || formError" ref="suratBalasanAlert" class="flex items-start gap-2 rounded-lg border border-red-300 bg-red-50 px-3 py-2.5" role="alert">
                                <svg viewBox="0 0 24 24" class="mt-0.5 h-4 w-4 shrink-0 text-red-600" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                                <div class="text-xs font-medium text-red-700">
                                    <p>{{ suratBalasanError }}</p>
                                    <p v-if="formError">{{ formError }}</p>
                                </div>
                            </div>
                            <button @click="handleApprove" :disabled="statusForm.processing" class="btn-success w-full text-center text-sm">
                                {{ statusForm.processing ? 'Memproses...' : 'Terima Pengajuan' }}
                            </button>
                            <button @click="showRevisionModal = true" :disabled="statusForm.processing" class="btn-warning w-full text-center text-sm">
                                Minta Revisi
                            </button>
                            <button @click="handleReject" :disabled="statusForm.processing" class="btn-danger w-full text-center text-sm">
                                {{ statusForm.processing ? 'Memproses...' : 'Tolak Pengajuan' }}
                            </button>
                        </template>
                        <div v-else class="rounded-lg bg-ink-100 p-3 text-center text-sm font-medium text-ink-700">
                            Status Pengajuan: <strong class="capitalize">{{ getStatusLabel(pengajuan.status) }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="glass-card p-6">
                    <h3 class="mb-4 font-display text-base font-bold text-ink-900">Timeline</h3>
                    <div class="relative space-y-5">
                        <div
                            v-for="(step, index) in steps"
                            :key="step.label"
                            class="relative flex items-start gap-3"
                        >
                            <div
                                v-if="index < steps.length - 1"
                                class="absolute left-3 top-6 h-full w-0.5 -ml-[1px] bg-ink-300/40"
                            />
                            <div class="relative z-10 grid h-5 w-5 shrink-0 place-items-center rounded-full">
                                <div v-if="step.status === 'completed'" class="grid h-5 w-5 place-items-center rounded-full bg-status-success text-white">
                                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                </div>
                                <div v-else-if="step.status === 'revision'" class="grid h-5 w-5 place-items-center rounded-full bg-amber-500 text-white ring-4 ring-amber-400/20">
                                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3">
                                        <line x1="12" y1="8" x2="12" y2="12"/>
                                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                                    </svg>
                                </div>
                                <div v-else-if="step.status === 'in_progress'" class="grid h-5 w-5 place-items-center rounded-full bg-forest-600 text-white ring-4 ring-forest-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse" />
                                </div>
                                <div v-else-if="step.status === 'rejected'" class="grid h-5 w-5 place-items-center rounded-full bg-status-danger text-white ring-4 ring-red-500/20">
                                    <svg viewBox="0 0 24 24" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3">
                                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                </div>
                                <div v-else class="h-5 w-5 rounded-full border-2 border-ink-300 bg-white" />
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-xs" :class="[
                                    step.status === 'completed' ? 'font-bold text-ink-900' : '',
                                    step.status === 'revision' ? 'font-bold text-amber-700' : '',
                                    step.status === 'in_progress' ? 'font-bold text-forest-700' : '',
                                    step.status === 'rejected' ? 'font-bold text-status-danger' : '',
                                    step.status === 'pending' ? 'font-medium text-ink-400' : ''
                                ]">
                                    {{ step.label }}
                                </p>
                                <p class="mt-0.5 text-[10px] text-ink-500">{{ step.date }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Minta Revisi -->
        <div v-if="showRevisionModal" class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4 backdrop-blur-sm">
            <div class="glass-panel w-full max-w-lg p-6 bg-white shadow-2xl rounded-2xl">
                <h3 class="font-display text-lg font-bold text-ink-900 mb-2">Minta Revisi Berkas</h3>
                <p class="text-xs text-ink-500 mb-4">
                    Tuliskan catatan atau alasan revisi berkas yang wajib diperbaiki oleh pemohon.
                </p>

                <div class="mb-4">
                    <label class="field-label" for="catatan_revisi">Catatan / Alasan Revisi <span class="text-red-600">*</span></label>
                    <textarea
                        id="catatan_revisi"
                        v-model="revisionForm.catatan_revisi"
                        rows="4"
                        class="field-input"
                        placeholder="Contoh: Surat pengantar belum ditandatangani oleh pimpinan kampus, mohon upload ulang surat bertanda tangan resmi."
                    ></textarea>
                    <p v-if="revisionError" class="mt-1.5 text-xs font-medium text-red-600">{{ revisionError }}</p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showRevisionModal = false" class="btn-secondary text-xs">Batal</button>
                    <button type="button" @click="submitRevision" :disabled="revisionForm.processing" class="btn-warning text-xs">
                        {{ revisionForm.processing ? 'Mengirim...' : 'Kirim Permintaan Revisi' }}
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
