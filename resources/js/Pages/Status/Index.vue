<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { getStatusLabel, getStatusBadgeClass, getTimelineSteps, formatDate } from '@/utils/statusLabel';

const props = defineProps({ pendaftaran: { type: Object, default: null } });

const steps = computed(() => getTimelineSteps(props.pendaftaran));
</script>

<template>
    <Head title="Status Pendaftaran" />
    <AppLayout title="Status Pendaftaran">
        <div v-if="pendaftaran" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Left Column: Pendaftaran Aktif (.glass-card) -->
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
                            {{ pendaftaran?.position?.nama ?? 'Data tidak tersedia' }}
                        </h3>
                        <p class="mt-0.5 text-sm text-ink-500">
                            {{ pendaftaran?.position?.division?.instansi ?? 'Data tidak tersedia' }}
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
            </section>

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
    </AppLayout>
</template>

