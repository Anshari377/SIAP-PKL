<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { getStatusLabel, getStatusBadgeClass, formatDate } from '@/utils/statusLabel';

defineProps({ riwayat: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Riwayat Pendaftaran" />
    <AppLayout title="Riwayat Pendaftaran">
        <section class="glass-panel mx-auto max-w-6xl p-6 sm:p-8">
            <div class="mb-7">
                <h2 class="font-display text-2xl font-bold text-ink-900">Riwayat Pendaftaran</h2>
                <p class="mt-1 text-sm text-ink-500">Lihat riwayat seluruh pengajuan pendaftaran PKL anda.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Bidang</th>
                            <th class="px-4 py-3">Posisi</th>
                            <th class="px-4 py-3">Instansi</th>
                            <th class="px-4 py-3">Tanggal Daftar</th>
                            <th class="px-4 py-3">Status Akhir</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="(item, index) in riwayat" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500">{{ index + 1 }}</td>
                            <td class="px-4 py-4 font-semibold text-ink-900">
                                {{ item.bidang }}
                                <p v-if="item.status === 'revision' && item.catatan_revisi" class="mt-1 text-xs text-amber-700 bg-amber-50 p-2 rounded border border-amber-200">
                                    Catatan Revisi: {{ item.catatan_revisi }}
                                </p>
                            </td>
                            <td class="px-4 py-4 font-medium text-ink-800">{{ item.posisi }}</td>
                            <td class="px-4 py-4 text-ink-700">{{ item.instansi }}</td>
                            <td class="px-4 py-4 text-ink-500">{{ formatDate(item.created_at) }}</td>
                            <td class="px-4 py-4">
                                <span :class="getStatusBadgeClass(item.status)" class="badge">
                                    {{ getStatusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    
                                    <a v-if="['accepted', 'rejected'].includes(item.status) && item.surat_balasan_path"
                                        :href="item.surat_balasan_path"
                                        target="_blank"
                                        class="btn-secondary px-3 py-1.5 text-xs"
                                    >
                                        Unduh Surat
                                    </a>
                                    <Link
                                        :href="route('status.index')"
                                        :class="item.status === 'revision' ? 'btn-warning px-3 py-1.5 text-xs' : 'btn-secondary px-3 py-1.5 text-xs'"
                                    >
                                        {{ item.status === 'revision' ? 'Upload Revisi' : 'Lihat Detail' }}
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="riwayat.length === 0">
                            <td colspan="7" class="px-4 py-10 text-center text-ink-500">
                                Belum ada riwayat pendaftaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>