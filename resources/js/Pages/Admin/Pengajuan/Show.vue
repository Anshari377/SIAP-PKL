<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, formatDate, getTimelineSteps } from '@/utils/statusLabel';

const props = defineProps({
    pengajuan: { type: Object, required: true },
});

const pengajuan = computed(() => props.pengajuan);
const ketua = computed(() => pengajuan.value.members?.[0] ?? {});
const anggota = computed(() => pengajuan.value.members ?? []);
const documentName = computed(() => pengajuan.value.document_path?.split('/').pop() ?? 'Berkas belum tersedia');
const steps = computed(() => getTimelineSteps(pengajuan.value));

const handleStatus = (status) => {
    router.patch(route('admin.pengajuan.status', pengajuan.value.id), { status });
};
</script>

<template>
    <Head title="Detail Pengajuan" />
    <AppLayout title="Detail Pengajuan PKL">
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
                            <p class="text-xs font-medium text-ink-500 uppercase tracking-wider">Asal Instansi</p>
                            <p class="mt-1 text-ink-800">{{ pengajuan.user?.agency?.name ?? '-' }}</p>
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
                        <button v-if="pengajuan.status === 'pending'" @click="handleStatus('accepted')" class="btn-success w-full text-center text-sm">
                            Terima Pengajuan
                        </button>
                        <button v-if="pengajuan.status === 'pending'" @click="handleStatus('rejected')" class="btn-danger w-full text-center text-sm">
                            Tolak Pengajuan
                        </button>
                        <div v-if="pengajuan.status !== 'pending'" class="rounded-lg bg-ink-100 p-3 text-center text-sm font-medium text-ink-500">
                            Pengajuan sudah diproses
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
    </AppLayout>
</template>
