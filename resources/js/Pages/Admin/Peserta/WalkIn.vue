<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { computed, ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    divisions: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    nim: '',
    school: '',
    major: '',
    phone: '',
    division_id: '',
    position_id: '',
    start_date: '',
    end_date: '',
});

// ─── Date helpers ───────────────────────────────────────────
const todayString = computed(() => {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
});

const minStartDate = computed(() => todayString.value);
const minEndDate = computed(() => {
    if (form.start_date && form.start_date >= todayString.value) return form.start_date;
    return todayString.value;
});

// ─── Selected division & positions ──────────────────────────
const selectedDivision = computed(() =>
    props.divisions.find((d) => String(d.id) === String(form.division_id)) ?? null
);

const availablePositions = computed(() => selectedDivision.value?.positions ?? []);

watch(
    () => form.division_id,
    () => {
        form.position_id = '';
        availability.value = null;
        const positions = selectedDivision.value?.positions ?? [];
        if (positions.length === 1) {
            form.position_id = String(positions[0].id);
        }
    }
);

// ─── Real-time availability check ───────────────────────────
const availability = ref(null);
const checking = ref(false);

const checkAvailability = async () => {
    if (!form.division_id || !form.start_date || !form.end_date) return;

    availability.value = null;
    checking.value = true;

    try {
        const response = await window.axios.get(route('admin.peserta.walk-in.check-availability'), {
            params: {
                division_id: form.division_id,
                start_date: form.start_date,
                end_date: form.end_date,
            },
        });
        availability.value = response.data;
    } catch {
        availability.value = null;
    } finally {
        checking.value = false;
    }
};

watch(
    () => [form.division_id, form.start_date, form.end_date],
    () => {
        availability.value = null;

        if (form.start_date && form.end_date && form.end_date < form.start_date) {
            form.end_date = form.start_date;
        }

        if (form.division_id && form.start_date && form.end_date && form.end_date >= form.start_date) {
            checkAvailability();
        }
    }
);

// ─── Readable date helper ────────────────────────────────────
const formatDateToInput = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const applyNextAvailableDate = () => {
    if (!availability.value?.next_available_date) return;

    const [ny, nm, nd] = availability.value.next_available_date.split('-').map(Number);

    if (form.start_date && form.end_date) {
        const [sy, sm, sd] = form.start_date.split('-').map(Number);
        const [ey, em, ed] = form.end_date.split('-').map(Number);
        const currentStart = new Date(sy, sm - 1, sd);
        const currentEnd = new Date(ey, em - 1, ed);

        const diffDays = Math.max(0, Math.round((currentEnd.getTime() - currentStart.getTime()) / (1000 * 60 * 60 * 24)));
        const newEnd = new Date(ny, nm - 1, nd + diffDays);

        form.start_date = availability.value.next_available_date;
        form.end_date = formatDateToInput(newEnd);
    } else {
        form.start_date = availability.value.next_available_date;
    }
};

const formatReadableDate = (dateStr) => {
    if (!dateStr) return '-';
    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

// ─── Submit ──────────────────────────────────────────────────
const canSubmit = computed(() =>
    !checking.value && availability.value?.available !== false
);

const submitForm = () => {
    if (!canSubmit.value) return;
    form.post(route('admin.peserta.walk-in.store'));
};
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
                    <!-- ─── Data Peserta ─────────────────────────────── -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-ink-500">Data Peserta</h3>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="field-label" for="name">Nama Lengkap</label>
                                <input id="name" v-model="form.name" type="text" required class="field-input" placeholder="Nama lengkap peserta" />
                                <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
                            </div>
                            <div>
                                <label class="field-label" for="nim">NIM / NIS <span class="text-ink-400">(Opsional)</span></label>
                                <input id="nim" v-model="form.nim" type="text" class="field-input" placeholder="Kosongkan jika tidak ada" />
                                <p v-if="form.errors.nim" class="field-error">{{ form.errors.nim }}</p>
                            </div>
                            <div>
                                <label class="field-label" for="phone">Nomor Telepon</label>
                                <input id="phone" v-model="form.phone" type="tel" required class="field-input" placeholder="08xx-xxxx-xxxx" />
                                <p v-if="form.errors.phone" class="field-error">{{ form.errors.phone }}</p>
                            </div>
                            <div>
                                <label class="field-label" for="school">Asal Sekolah / Kampus</label>
                                <input id="school" v-model="form.school" type="text" required class="field-input" placeholder="Nama sekolah atau universitas" />
                                <p v-if="form.errors.school" class="field-error">{{ form.errors.school }}</p>
                            </div>
                            <div>
                                <label class="field-label" for="major">Jurusan / Program Studi</label>
                                <input id="major" v-model="form.major" type="text" required class="field-input" placeholder="Nama jurusan" />
                                <p v-if="form.errors.major" class="field-error">{{ form.errors.major }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- ─── Bidang & Jadwal ─────────────────────────── -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-ink-500">Bidang PKL &amp; Jadwal</h3>
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="field-label" for="division_id">Bidang PKL</label>
                                <select id="division_id" v-model="form.division_id" required class="field-input">
                                    <option value="" disabled>-- Pilih bidang --</option>
                                    <option v-for="d in divisions" :key="d.id" :value="String(d.id)">
                                        {{ d.nama }} (kuota {{ d.kuota_terisi }}/{{ d.quota }})
                                    </option>
                                </select>
                                <!-- General quota badge -->
                                <div v-if="selectedDivision" class="mt-2 flex items-center gap-2 text-xs">
                                    <span
                                        :class="selectedDivision.kuota_sisa > 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                                        class="rounded-full px-2.5 py-0.5 font-semibold"
                                    >
                                        {{ selectedDivision.kuota_sisa > 0 ? `${selectedDivision.kuota_sisa} slot tersedia` : 'Kuota penuh' }}
                                    </span>
                                    <span class="text-ink-500">saat ini (keseluruhan)</span>
                                </div>
                                <p v-if="form.errors.division_id" class="field-error">{{ form.errors.division_id }}</p>
                            </div>

                            <!-- Position -->
                            <div v-if="availablePositions.length > 1" class="sm:col-span-2">
                                <label class="field-label" for="position_id">Posisi PKL</label>
                                <select id="position_id" v-model="form.position_id" class="field-input">
                                    <option value="">-- Pilih posisi --</option>
                                    <option v-for="pos in availablePositions" :key="pos.id" :value="String(pos.id)">{{ pos.nama }}</option>
                                </select>
                                <p v-if="form.errors.position_id" class="field-error">{{ form.errors.position_id }}</p>
                            </div>
                            <div v-else-if="selectedDivision && availablePositions.length === 1" class="sm:col-span-2">
                                <p class="text-xs text-ink-500">Posisi PKL: <strong class="text-ink-800">{{ availablePositions[0].nama }}</strong></p>
                            </div>

                            <div>
                                <label class="field-label" for="start_date">Tanggal Mulai</label>
                                <input id="start_date" v-model="form.start_date" type="date" :min="minStartDate" required class="field-input" />
                                <p v-if="form.errors.start_date" class="field-error">{{ form.errors.start_date }}</p>
                            </div>
                            <div>
                                <label class="field-label" for="end_date">Tanggal Selesai</label>
                                <input id="end_date" v-model="form.end_date" type="date" :min="minEndDate" required class="field-input" />
                                <p v-if="form.errors.end_date" class="field-error">{{ form.errors.end_date }}</p>
                            </div>

                            <!-- ─── Real-time availability ─────────── -->
                            <div class="sm:col-span-2">
                                <!-- Checking spinner -->
                                <div v-if="checking" class="flex items-center gap-2 rounded-xl border border-ink-300/40 bg-ink-50 px-4 py-3 text-sm text-ink-600">
                                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 12a9 9 0 11-6.219-8.56" stroke-linecap="round"/>
                                    </svg>
                                    Memeriksa ketersediaan slot untuk periode ini...
                                </div>

                                <!-- Available -->
                                <div
                                    v-else-if="availability?.available === true"
                                    class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700"
                                >
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>
                                        Kuota tersedia untuk periode
                                        <strong>{{ formatReadableDate(form.start_date) }} – {{ formatReadableDate(form.end_date) }}</strong>.
                                        Slot terisi: <strong>{{ availability.slot_terisi_periode }}/{{ availability.kuota_total }}</strong>,
                                        tersisa <strong>{{ availability.slot_tersedia }}</strong> slot.
                                    </span>
                                </div>

                                <!-- Full -->
                                <div
                                    v-else-if="availability?.available === false"
                                    class="flex flex-col gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div class="flex items-start gap-2.5">
                                        <svg viewBox="0 0 24 24" class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                            <line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                                        </svg>
                                        <div>
                                            <p>
                                                Seluruh <strong>{{ availability.kuota_total }} slot</strong> sudah terisi penuh
                                                untuk periode ini (terisi: {{ availability.slot_terisi_periode }}).
                                            </p>
                                            <p v-if="availability.next_available_date" class="mt-1 text-xs text-red-800">
                                                Periode berikutnya dengan kuota yang cukup tersedia mulai:
                                                <strong class="font-bold underline">{{ formatReadableDate(availability.next_available_date) }}</strong>.
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        v-if="availability.next_available_date"
                                        type="button"
                                        @click="applyNextAvailableDate"
                                        class="inline-flex shrink-0 items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 active:scale-95"
                                    >
                                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="9 11 12 14 22 4"/>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                                        </svg>
                                        Terapkan Tanggal Ini
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ─── Actions ────────────────────────────────── -->
                    <div class="flex items-center justify-end gap-3 border-t border-ink-300/30 pt-4">
                        <Link :href="route('admin.peserta.index')" class="btn-secondary">Batal</Link>
                        <button
                            type="submit"
                            class="btn-primary disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing || !canSubmit"
                        >
                            <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12a9 9 0 11-6.219-8.56" stroke-linecap="round"/>
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : 'Daftarkan Peserta' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>