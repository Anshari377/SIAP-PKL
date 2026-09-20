<script setup>
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    instansiList: {
        type: Array,
        default: () => [],
    },
    adminUsers: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const form = useForm({
    agency_id: props.instansiList.length > 0 ? props.instansiList[0].id : '',
    email: '',
});

// Set default agency_id when instansiList updates
watch(() => props.instansiList, (newVal) => {
    if (newVal.length > 0 && !form.agency_id) {
        form.agency_id = newVal[0].id;
    }
}, { immediate: true });

const statusBadge = (status) => {
    if (status === 'pending') return 'badge-warning';
    if (status === 'claimed') return 'badge-success';
    return 'badge-danger';
};

const statusLabel = (status) => {
    if (status === 'pending') return 'Menunggu Klaim';
    if (status === 'claimed') return 'Sudah Diklaim';
    return 'Dibatalkan';
};

const submitUndangan = () => {
    form.post(route('superadmin.undangan.store'), {
        onSuccess: () => {
            form.reset('email');
        },
    });
};

const batalkanUndangan = (item) => {
    if (confirm(`Yakin ingin membatalkan undangan/akses admin untuk ${item.email}?`)) {
        router.delete(route('superadmin.undangan.destroy', item.id));
    }
};
</script>

<template>
    <Head title="Undangan Admin Instansi" />
    <SuperAdminLayout title="Undangan Admin Instansi">
        <div class="mb-6">
            <h2 class="font-display text-xl font-bold text-ink-900">Undang Admin Instansi</h2>
            <p class="mt-1 text-sm text-ink-500">Kirim undangan ke calon admin untuk mengelola portal instansi.</p>
        </div>

        <!-- Success Alert -->
        <div v-if="page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
            {{ page.props.flash.success }}
        </div>

        <!-- Form Undangan -->
        <div class="glass-panel mb-6 p-6 sm:p-8">
            <h3 class="mb-4 font-display text-base font-bold text-ink-900">Kirim Undangan Baru</h3>
            <form @submit.prevent="submitUndangan" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="field-label">Pilih Instansi</label>
                    <select v-model="form.agency_id" class="field-input" required>
                        <option v-for="inst in instansiList" :key="inst.id" :value="inst.id">
                            {{ inst.name }}
                        </option>
                    </select>
                    <div v-if="form.errors.agency_id" class="mt-1 text-xs text-rose-500">{{ form.errors.agency_id }}</div>
                </div>
                <div>
                    <label class="field-label">Email Calon Admin</label>
                    <input v-model="form.email" type="email" required placeholder="calon.admin@instansi.co.id" class="field-input" />
                    <div v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</div>
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" :disabled="form.processing" class="btn-primary">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" /><polyline points="22,6 12,13 2,6" />
                        </svg>
                        {{ form.processing ? 'Mengirim...' : 'Kirim Undangan' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Riwayat Undangan -->
        <section class="glass-panel p-6 sm:p-8">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-display text-base font-bold text-ink-900">Riwayat Undangan / Admin</h3>
                <span class="badge badge-info">{{ adminUsers.length }} Admin</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="border-b border-ink-300/35 text-xs uppercase tracking-wider text-ink-500">
                        <tr>
                            <th class="px-4 py-3 w-12">No</th>
                            <th class="px-4 py-3">Email Admin</th>
                            <th class="px-4 py-3">Instansi Tujuan</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Tanggal Terdaftar</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-300/20">
                        <tr v-for="(item, index) in adminUsers" :key="item.id" class="transition hover:bg-forest-50/60">
                            <td class="px-4 py-4 text-ink-500">{{ index + 1 }}</td>
                            <td class="px-4 py-4 font-semibold text-ink-900">
                                <div>{{ item.email }}</div>
                                <div class="text-xs font-normal text-ink-500">{{ item.nama }}</div>
                            </td>
                            <td class="px-4 py-4 text-ink-700">{{ item.instansi }}</td>
                            <td class="px-4 py-4">
                                <span :class="statusBadge(item.status)" class="badge">
                                    {{ statusLabel(item.status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-ink-500 whitespace-nowrap">{{ item.tanggal }}</td>
                            <td class="px-4 py-4 text-right">
                                <button @click="batalkanUndangan(item)" class="btn-danger px-3 py-1.5 text-xs">
                                    Cabut Akses
                                </button>
                            </td>
                        </tr>
                        <tr v-if="adminUsers.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-ink-500">
                                Belum ada undangan / admin instansi terdaftar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </SuperAdminLayout>
</template>
