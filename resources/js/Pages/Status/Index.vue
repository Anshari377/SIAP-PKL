<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, getTimelineSteps, formatDate } from '@/utils/statusLabel';

const props = defineProps({ pendaftaran: { type: Object, default: null } });

const steps = computed(() => getTimelineSteps(props.pendaftaran));

const showReuploadModal = ref(false);
const reuploadError = ref('');

const reuploadForm = useForm({
    document: null,
});

const onFilePicked = (event) => {
    const file = event.target.files[0];
    if (!file) {
        reuploadForm.document = null;
        return;
    }

    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    const isMaxFiveMb = file.size <= 5 * 1024 * 1024;

    if (!isPdf || !isMaxFiveMb) {
        reuploadError.value = !isPdf ? 'File harus berformat PDF.' : 'Ukuran file maksimal 5MB.';
        reuploadForm.document = null;
        event.target.value = '';
        return;
    }

    reuploadError.value = '';
    reuploadForm.document = file;
};

const submitReupload = () => {
    if (!reuploadForm.document) {
        reuploadError.value = 'Silakan pilih berkas PDF terlebih dahulu.';
        return;
    }

    reuploadForm.post(route('pengajuan.reupload', props.pendaftaran.id), {
        forceFormData: true,
        onSuccess: () => {
            showReuploadModal.value = false;
            reuploadForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Status Pendaftaran" />
    <AppLayout title="Status Pendaftaran">
        <div v-if="pendaftaran" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Left Column: Pendaftaran Aktif (.glass-card) -->
            <div class="space-y-6">
                <!-- Revision Note Card if application status = revision -->
                <div v-if="pendaftaran.status === 'revision' || pendaftaran.catatan_revisi" class="rounded-2xl border border-amber-300 bg-amber-50/90 p-6 shadow-sm">
                    <div class="flex items-center gap-2.5 text-amber-800 font-bold mb-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        Perlu Revisi Berkas
                    </div>
                    <p class="text-sm leading-relaxed text-amber-900 bg-white/70 p-3.5 rounded-xl border border-amber-200 mb-4">
                        {{ pendaftaran.catatan_revisi || 'Silakan unggah ulang berkas pengajuan PKL sesuai petunjuk verifikator.' }}
                    </p>
                    <button @click="showReuploadModal = true" class="btn-warning w-full text-center text-sm font-semibold">
                        Upload Ulang Berkas Revisi
                    </button>
                </div>

                <section class="glass-card p-6 h-fit">
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="font-display text-lg font-bold text-ink-900">Pendaftaran Aktif</h2>
                        <Link :href="route('riwayat.index')" class="text-xs font-semibold text-forest-700 hover:underline">Lihat semua</Link>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-forest-600/10 text-forest-700">
                            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-display text-base font-bold text-ink-900">
                                {{ pendaftaran?.division?.nama ?? pendaftaran?.position?.nama ?? 'Data tidak tersedia' }}
                            </h3>
                            <p class="mt-0.5 text-sm text-ink-500">
                                {{ pendaftaran?.division?.instansi ?? pendaftaran?.position?.division?.instansi ?? 'Data tidak tersedia' }}
                            </p>
                            <p class="mt-1 text-xs text-ink-500">
                                Daftar: {{ formatDate(pendaftaran?.created_at) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-ink-300/30 flex items-center justify-between">
                        <span class="text-xs text-ink-500 font-medium">Status Pengajuan</span>
                        <span :class="getStatusBadgeClass(pendaftaran?.status)" class="badge">
                            {{ getStatusLabel(pendaftaran?.status) }}
                        </span>
                    </div>

                    <div v-if="pendaftaran.status === 'revision'" class="mt-4 pt-3 border-t border-ink-300/20 text-center">
                        <button @click="showReuploadModal = true" class="btn-warning w-full text-xs py-2">
                            Upload Ulang Berkas
                        </button>
                    </div>
                </section>
            </div>

            <!-- Right Column: Timeline Status (.glass-card) -->
            <section class="glass-card p-6">
                <h2 class="mb-6 font-display text-lg font-bold text-ink-900">Timeline Status</h2>

                <div class="relative space-y-6">
                    <div
                        v-for="(step, index) in steps"
                        :key="step.label"
                        class="relative flex items-start gap-4"
                    >
                        <!-- Vertical line connector -->
                        <div
                            v-if="index < steps.length - 1"
                            class="absolute left-3 top-6 h-full w-0.5 -ml-[1px] bg-ink-300/40"
                        />

                        <!-- Circle Node -->
                        <div class="relative z-10 grid h-6 w-6 shrink-0 place-items-center rounded-full">
                            <!-- Completed: centang hijau -->
                            <div v-if="step.status === 'completed'" class="grid h-6 w-6 place-items-center rounded-full bg-status-success text-white">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>

                            <!-- Revision: lingkaran amber dengan tanda seru -->
                            <div v-else-if="step.status === 'revision'" class="grid h-6 w-6 place-items-center rounded-full bg-amber-500 text-white ring-4 ring-amber-400/20">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3">
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>

                            <!-- In Progress: lingkaran forest/emerald berdenyut -->
                            <div v-else-if="step.status === 'in_progress'" class="grid h-6 w-6 place-items-center rounded-full bg-forest-600 text-white ring-4 ring-forest-500/20">
                                <span class="h-2 w-2 rounded-full bg-white animate-pulse" />
                            </div>

                            <!-- Rejected: lingkaran merah dengan ikon silang -->
                            <div v-else-if="step.status === 'rejected'" class="grid h-6 w-6 place-items-center rounded-full bg-status-danger text-white ring-4 ring-red-500/20">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </div>

                            <!-- Pending: lingkaran abu kosong -->
                            <div v-else class="h-6 w-6 rounded-full border-2 border-ink-300 bg-white" />
                        </div>

                        <!-- Step Info -->
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p
                                class="text-sm"
                                :class="[
                                    step.status === 'completed' ? 'font-bold text-ink-900' : '',
                                    step.status === 'revision' ? 'font-bold text-amber-700' : '',
                                    step.status === 'in_progress' ? 'font-bold text-forest-700' : '',
                                    step.status === 'rejected' ? 'font-bold text-status-danger' : '',
                                    step.status === 'pending' ? 'font-medium text-ink-400' : ''
                                ]"
                            >
                                {{ step.label }}
                            </p>
                            <p class="mt-0.5 text-xs text-ink-500">{{ step.date }}</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Empty State if no pendaftaran -->
        <div v-else class="glass-panel mx-auto max-w-2xl p-8 text-center rounded-3xl">
            <div class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-forest-500/10 text-forest-700">
                <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                </svg>
            </div>
            <h2 class="font-display text-xl font-bold text-ink-900">Belum Ada Pengajuan PKL</h2>
            <p class="mt-2 text-sm text-ink-500">Anda belum mendaftar pada posisi atau bidang PKL manapun.</p>
            <div class="mt-6">
                <Link :href="route('bidang.index')" class="btn-primary px-6 py-2.5">
                    Lihat Bidang PKL
                </Link>
            </div>
        </div>

        <!-- Modal Upload Ulang Berkas -->
        <div v-if="showReuploadModal" class="fixed inset-0 z-50 flex items-center justify-center bg-ink-900/50 p-4 backdrop-blur-sm">
            <div class="glass-panel w-full max-w-lg p-6 bg-white shadow-2xl rounded-2xl">
                <h3 class="font-display text-lg font-bold text-ink-900 mb-2">Upload Ulang Berkas Revisi</h3>
                <p class="text-xs text-ink-500 mb-4">
                    Unggah berkas PDF pengajuan baru untuk menggantikan berkas sebelumnya.
                </p>

                <div class="mb-5">
                    <label class="field-label" for="reupload_doc">Surat Pengantar / Proposal Baru (PDF)</label>
                    <div class="flex flex-wrap items-center gap-3">
                        <label
                            for="reupload_doc"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-ink-300 bg-white px-4 py-2 text-sm font-medium text-ink-700 hover:bg-surface"
                        >
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                            {{ reuploadForm.document?.name ? 'Ganti File PDF' : 'Pilih File PDF' }}
                        </label>
                        <input id="reupload_doc" type="file" accept=".pdf,application/pdf" class="hidden" @change="onFilePicked" />
                        <span v-if="reuploadForm.document?.name" class="text-xs font-semibold text-forest-700 truncate max-w-[200px]">
                            ✓ {{ reuploadForm.document.name }}
                        </span>
                    </div>
                    <p class="mt-1.5 text-xs text-ink-500">Format PDF, maksimal 5MB.</p>
                    <p v-if="reuploadError || reuploadForm.errors.document" class="mt-1.5 text-xs font-medium text-red-600">
                        {{ reuploadError || reuploadForm.errors.document }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="showReuploadModal = false" class="btn-secondary text-xs">Batal</button>
                    <button
                        type="button"
                        @click="submitReupload"
                        class="btn-primary text-xs"
                        :disabled="reuploadForm.processing || !reuploadForm.document"
                    >
                        {{ reuploadForm.processing ? 'Mengunggah...' : 'Kirim Berkas Revisi' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

