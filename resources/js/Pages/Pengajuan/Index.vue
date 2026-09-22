<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    divisions: { type: Array, default: () => [] },
    hasActiveApplication: { type: Boolean, default: false },
    activeApplication: { type: Object, default: null },
});

const page = usePage();
const authName = page.props.auth?.user?.name ?? '';

const initialDivision = new URLSearchParams(window.location.search).get('division') || '';

const form = useForm({
    division_id: initialDivision,
    position_id: '',
    start_date: '',
    end_date: '',
    tipe: 'individu',
    ketua: {
        name: authName,
        nim: '',
        school: '',
        major: '',
        phone: '',
    },
    document: null,
    consent_pdp: false,
});

const formatDateInput = (dateValue) => {
    if (!dateValue) return '';

    const date = new Date(`${dateValue}T00:00:00`);
    if (Number.isNaN(date.getTime())) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
};

const todayString = computed(() => {
    const now = new Date();
    return formatDateInput(`${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`);
});

const minStartDate = computed(() => todayString.value);
const minEndDate = computed(() => {
    if (form.start_date) return form.start_date >= todayString.value ? form.start_date : todayString.value;
    return todayString.value;
});

const sanitizePastDate = (value) => {
    if (!value) return value;

    const selected = new Date(`${value}T00:00:00`);
    const today = new Date(`${todayString.value}T00:00:00`);

    if (selected < today) {
        return todayString.value;
    }

    return value;
};

const members = ref([]);
const frontErrors = ref({});
const availability = ref(null);
const checking = ref(false);

const addMember = () => {
    members.value.push({
        name: '',
        nim: '',
        school: form.ketua.school || '',
        major: form.ketua.major || '',
        phone: '',
    });
};

const removeMember = (index) => {
    members.value.splice(index, 1);
};

watch(
    members,
    () => {
        if (frontErrors.value.members) {
            delete frontErrors.value.members;
        }
    },
    { deep: true },
);

const selectedDivision = computed(() => {
    return props.divisions.find((d) => String(d.id) === String(form.division_id)) || null;
});

const availablePositions = computed(() => {
    return selectedDivision.value?.positions ?? [];
});

const sisaKuota = computed(() => {
    if (availability.value?.slot_tersedia !== undefined) {
        return Number(availability.value.slot_tersedia);
    }
    if (!selectedDivision.value) return 0;
    return Number(selectedDivision.value.kuota_sisa ?? selectedDivision.value.kuota ?? 0);
});

const anggotaBatas = computed(() => Math.max(0, sisaKuota.value - 1));

const canAddMember = computed(() => {
    if (form.tipe !== 'kelompok') return false;
    if (!form.division_id) return true;
    if (availability.value?.slot_tersedia !== undefined) {
        return members.value.length < anggotaBatas.value;
    }
    if (!form.start_date || !form.end_date || checking.value) {
        return true;
    }
    return members.value.length < anggotaBatas.value;
});

const quotaLimitMessage = computed(() => {
    if (form.tipe !== 'kelompok') return '';
    if (!form.division_id || !form.start_date || !form.end_date) return '';
    if (checking.value) return '';

    if (availability.value?.available === true) {
        if (sisaKuota.value <= 1) {
            return `Sisa kuota bidang pada periode ini hanya ${sisaKuota.value} slot (hanya cukup untuk ketua), tidak dapat menambah anggota kelompok.`;
        }
        if (members.value.length >= anggotaBatas.value) {
            return `Maksimal anggota untuk periode ini adalah ${anggotaBatas.value} orang (Total slot: 1 ketua + ${members.value.length} anggota = ${sisaKuota.value} slot).`;
        }
    }
    return '';
});

const formatReadableDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
};

const friendlyError = (field, error) => {
    if (!error) return '';
    const lower = error.toLowerCase();
    if (lower.includes('has already been taken') || lower.includes('sudah terdaftar') || lower.includes('sudah digunakan')) {
        if (field.includes('phone')) return 'Nomor HP ini sudah terdaftar oleh peserta lain.';
        if (field.includes('nim')) return 'NIM/NISN ini sudah terdaftar, pastikan tidak ada kesalahan input.';
    }
    return error;
};

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

const checkAvailability = async () => {
    if (!form.division_id || !form.start_date || !form.end_date) return;

    frontErrors.value = {};
    availability.value = null;
    checking.value = true;

    try {
        const requiredSlots = form.tipe === 'kelompok' ? (1 + members.value.length) : 1;
        const response = await window.axios.get(route('pengajuan.check-availability'), {
            params: {
                division_id: form.division_id,
                start_date: form.start_date,
                end_date: form.end_date,
                required_slots: requiredSlots,
            },
        });
        availability.value = response.data;
    } catch (error) {
        availability.value = null;
    } finally {
        checking.value = false;
    }
};

watch(
    () => form.division_id,
    () => {
        // Reset position when division changes
        form.position_id = '';
        availability.value = null;
        // Auto-select if only one position available
        const positions = selectedDivision.value?.positions ?? [];
        if (positions.length === 1) {
            form.position_id = String(positions[0].id);
        }
    }
);

watch(
    () => [form.division_id, form.start_date, form.end_date, form.tipe, members.value.length],
    () => {
        availability.value = null;

        if (form.start_date) {
            form.start_date = sanitizePastDate(form.start_date);
        }

        if (form.end_date) {
            form.end_date = sanitizePastDate(form.end_date);
        }

        if (form.start_date && form.end_date && form.end_date < form.start_date) {
            form.end_date = form.start_date;
        }

        if (form.division_id && form.start_date && form.end_date && form.end_date >= form.start_date) {
            checkAvailability();
        }
    }
);

const invalidDateRange = computed(() => {
    return form.start_date && form.end_date && form.end_date < form.start_date;
});

const onFilePicked = (event) => {
    const file = event.target.files[0];
    if (!file) {
        form.document = null;
        return;
    }

    const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
    const isMaxFiveMb = file.size <= 5 * 1024 * 1024;

    if (!isPdf || !isMaxFiveMb) {
        frontErrors.value.document = !isPdf
            ? 'File harus berformat PDF.'
            : 'Ukuran file maksimal 5MB.';
        form.document = null;
        event.target.value = '';
        return;
    }

    frontErrors.value.document = null;
    form.document = file;
    event.target.value = '';
};

const fileName = computed(() => form.document?.name || '');

const stepUpload = computed(() => (form.tipe === 'kelompok' ? 4 : 3));

const validateFrontend = () => {
    const errors = {};

    if (!form.division_id) errors.division_id = 'Pilih bidang PKL terlebih dahulu.';
    if (!form.start_date) errors.start_date = 'Tanggal mulai wajib diisi.';
    if (form.start_date && form.start_date < todayString.value) {
        errors.start_date = 'Tanggal mulai PKL tidak boleh sebelum hari ini.';
    }
    if (!form.end_date) errors.end_date = 'Tanggal selesai wajib diisi.';
    if (form.end_date && form.end_date < todayString.value) {
        errors.end_date = 'Tanggal selesai PKL tidak boleh sebelum hari ini.';
    }
    if (invalidDateRange.value) errors.end_date = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';

    if (!form.tipe) errors.tipe = 'Pilih tipe pendaftaran.';

    if (!form.ketua.name.trim()) errors['ketua.name'] = 'Nama ketua wajib diisi.';
    if (!form.ketua.nim.trim()) errors['ketua.nim'] = 'NIM/NISN wajib diisi.';
    if (!form.ketua.school.trim()) errors['ketua.school'] = 'Sekolah/Kampus ketua wajib diisi.';
    if (!form.ketua.major.trim()) errors['ketua.major'] = 'Jurusan ketua wajib diisi.';

    if (form.tipe === 'kelompok') {
        if (members.value.length === 0) {
            errors.members = 'Minimal tambahkan 1 anggota kelompok.';
        } else {
            const invalidIdx = members.value.findIndex(
                (m) => !m.name?.trim() || !m.nim?.trim()
            );
            if (invalidIdx >= 0) {
                errors.members = `Data anggota ke-${invalidIdx + 1} belum lengkap (Nama dan NIM/NISN wajib diisi).`;
            }
        }
    }

    if (!form.document) {
        errors.document = 'Surat Pengantar / Proposal (PDF) wajib diunggah.';
    }

    if (!form.consent_pdp) {
        errors.consent_pdp = 'Anda wajib menyetujui pemrosesan data pribadi sesuai Undang-Undang Nomor 27 Tahun 2022 (UU PDP).';
    }

    frontErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const canSubmit = computed(() => {
    // Harus ada cek availability yang sudah selesai dan hasilnya 'tersedia'
    if (checking.value || availability.value?.available !== true || !form.consent_pdp) return false;
    if (form.tipe === 'kelompok' && availability.value?.slot_tersedia !== undefined) {
        return (1 + members.value.length) <= availability.value.slot_tersedia;
    }
    return true;
});

const submit = () => {
    if (!validateFrontend()) return;
    // Blokir jika kuota tidak tersedia ATAU cek belum selesai
    if (availability.value?.available !== true) return;

    form.transform((data) => ({
        ...data,
        ketua: { ...data.ketua },
        members: members.value.map((m) => ({
            ...m,
            school: m.school || data.ketua.school,
            major: m.major || data.ketua.major,
        })),
    })).post(route('pengajuan.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            members.value = [];
        },
    });
};
</script>

<template>
    <Head title="Pengajuan PKL" />
    <AppLayout title="Pengajuan PKL">
        <!-- If user already has an active application -->
        <div v-if="hasActiveApplication" class="mx-auto max-w-3xl glass-panel p-8 text-center rounded-3xl space-y-6">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-amber-500/10 text-amber-600">
                <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
            </div>
            <div>
                <h2 class="font-display text-2xl font-bold text-ink-900">Permohonan Aktif Ditemukan</h2>
                <p class="mt-2 text-sm text-ink-600 max-w-xl mx-auto leading-relaxed">
                    Anda sudah memiliki permohonan aktif, silakan selesaikan atau tunggu prosesnya sebelum mengajukan permohonan baru.
                </p>
            </div>

            <div v-if="activeApplication" class="rounded-2xl border border-ink-300/40 bg-white/70 p-5 text-left max-w-md mx-auto shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-ink-500 font-medium">Permohonan Berjalan</span>
                    <span class="badge badge-revision">
                        {{ activeApplication.status === 'revision' ? 'Perlu Revisi' : (activeApplication.status === 'accepted' ? 'Diterima' : 'Dalam Proses') }}
                    </span>
                </div>
                <p class="font-bold text-ink-900">{{ activeApplication.division_nama }}</p>
                <p class="text-xs text-ink-500 mt-1">{{ activeApplication.instansi }} · Tanggal: {{ activeApplication.created_at }}</p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                <Link :href="route('status.index')" class="btn-primary px-6 py-2.5">
                    Lihat Status Permohonan
                </Link>
                <Link :href="route('riwayat.index')" class="btn-secondary px-6 py-2.5">
                    Lihat Riwayat Pendaftaran
                </Link>
            </div>
        </div>

        <form v-else @submit.prevent="submit" enctype="multipart/form-data" class="mx-auto max-w-5xl space-y-6">
            <!-- Server / global error -->
            <div
                v-if="form.errors.message"
                class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700"
            >
                {{ form.errors.message }}
            </div>

            <!-- 1. Pemilihan Bidang + 2. Rentang Tanggal -->
            <section class="glass-panel p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-forest-600/10 text-sm font-bold text-forest-700">1</span>
                    <div>
                        <h2 class="font-display text-lg font-bold text-ink-900">Bidang PKL &amp; Rentang Tanggal</h2>
                        <p class="text-xs text-ink-500">Pilih bidang yang kamu minati beserta periode pelaksanaan PKL.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div>
                        <label class="field-label" for="ketua.school">Sekolah / Kampus</label>
                        <input id="ketua.school" v-model="form.ketua.school" type="text" class="field-input" />
                        <p v-if="frontErrors['ketua.school'] || form.errors['ketua.school']" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors['ketua.school'] || form.errors['ketua.school'] }}
                        </p>
                    </div>
                    <div>
                        <label class="field-label" for="ketua.major">Jurusan</label>
                        <input id="ketua.major" v-model="form.ketua.major" type="text" class="field-input" />
                        <p v-if="frontErrors['ketua.major'] || form.errors['ketua.major']" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors['ketua.major'] || form.errors['ketua.major'] }}
                        </p>
                    </div>
                    <div class="md:col-span-3">
                        <label class="field-label" for="division_id">Bidang PKL</label>
                        <select id="division_id" v-model="form.division_id" class="field-input">
                            <option value="">-- Pilih Bidang PKL --</option>
                            <option v-for="division in divisions" :key="division.id" :value="String(division.id)">
                                {{ division.nama }} — {{ division.instansi }} (kuota {{ division.kuota_terisi ?? (division.kuota - division.kuota_sisa) }}/{{ division.kuota }})
                            </option>
                        </select>
                        <p v-if="frontErrors.division_id || form.errors.division_id" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors.division_id || form.errors.division_id }}
                        </p>
                    </div>

                    <!-- Posisi PKL — tampil hanya jika bidang dipilih dan memiliki > 1 posisi -->
                    <div v-if="selectedDivision && availablePositions.length > 1" class="md:col-span-3">
                        <select id="position_id" v-model="form.position_id" class="field-input">
                            <option value="">-- Pilih Posisi --</option>
                            <option v-for="pos in availablePositions" :key="pos.id" :value="String(pos.id)">
                                {{ pos.nama }}
                            </option>
                        </select>
                        <p v-if="frontErrors.position_id || form.errors.position_id" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors.position_id || form.errors.position_id }}
                        </p>
                    </div>

                    <div>
                        <label class="field-label" for="start_date">Tanggal Mulai</label>
                        <input
                            id="start_date"
                            v-model="form.start_date"
                            type="date"
                            :min="minStartDate"
                            @change="form.start_date = sanitizePastDate(form.start_date)"
                            class="field-input"
                        />
                        <p v-if="frontErrors.start_date || form.errors.start_date" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors.start_date || form.errors.start_date }}
                        </p>
                    </div>

                    <div>
                        <label class="field-label" for="end_date">Tanggal Selesai</label>
                        <input
                            id="end_date"
                            v-model="form.end_date"
                            type="date"
                            :min="minEndDate"
                            @change="form.end_date = sanitizePastDate(form.end_date)"
                            class="field-input"
                        />
                        <p v-if="frontErrors.end_date || form.errors.end_date" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors.end_date || form.errors.end_date }}
                        </p>
                    </div>

                    <!-- Real-time availability -->
                    <div class="md:col-span-3">
                        <div v-if="checking" class="flex items-center gap-2 rounded-xl border border-ink-300/40 bg-ink-50 px-4 py-3 text-sm text-ink-600">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12a9 9 0 11-6.219-8.56" stroke-linecap="round"/>
                            </svg>
                            Memeriksa ketersediaan kuota...
                        </div>

                        <div
                            v-else-if="availability?.available === true"
                            class="space-y-2"
                        >
                            <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span>
                                    Kuota tersedia untuk periode
                                    <strong>{{ formatReadableDate(form.start_date) }} – {{ formatReadableDate(form.end_date) }}</strong>.
                                    Slot terisi pada periode ini: <strong>{{ availability.slot_terisi_periode }}/{{ availability.kuota_total }}</strong>,
                                    tersisa <strong>{{ availability.slot_tersedia }}</strong> slot.
                                </span>
                            </div>
                        </div>

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
            </section>

            <!-- 3. Tipe Pendaftaran + 4. Data Ketua -->
            <section class="glass-panel p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-forest-600/10 text-sm font-bold text-forest-700">2</span>
                    <div>
                        <h2 class="font-display text-lg font-bold text-ink-900">Tipe Pendaftaran &amp; Data Ketua</h2>
                        <p class="text-xs text-ink-500">Pilih tipe pendaftaran dan lengkapi data ketua (pemohon utama).</p>
                    </div>
                </div>

                <div class="mb-6 flex gap-6">
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-ink-800">
                        <input v-model="form.tipe" type="radio" value="individu" class="h-4 w-4 accent-forest-600" />
                        Individu
                    </label>
                    <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-ink-800">
                        <input v-model="form.tipe" type="radio" value="kelompok" class="h-4 w-4 accent-forest-600" />
                        Kelompok
                    </label>
                </div>
                <p v-if="frontErrors.tipe || form.errors.tipe" class="mb-4 -mt-3 text-xs font-medium text-red-600">
                    {{ frontErrors.tipe || form.errors.tipe }}
                </p>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label" for="ketua.name">Nama Ketua</label>
                        <input id="ketua.name" v-model="form.ketua.name" type="text" class="field-input" />
                        <p v-if="frontErrors['ketua.name'] || form.errors['ketua.name']" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors['ketua.name'] || form.errors['ketua.name'] }}
                        </p>
                    </div>
                    <div>
                        <label class="field-label" for="ketua.nim">NIM / NISN <span class="text-red-600">*</span></label>
                        <input id="ketua.nim" v-model="form.ketua.nim" type="text" required class="field-input" />
                        <p v-if="frontErrors['ketua.nim'] || friendlyError('ketua.nim', form.errors['ketua.nim'])" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors['ketua.nim'] || friendlyError('ketua.nim', form.errors['ketua.nim']) }}
                        </p>
                    </div>
                    <div>
                        <label class="field-label" for="ketua.phone">No HP <span class="text-ink-400">(opsional)</span></label>
                        <input id="ketua.phone" v-model="form.ketua.phone" type="text" inputmode="tel" class="field-input" />
                        <p v-if="frontErrors['ketua.phone'] || friendlyError('ketua.phone', form.errors['ketua.phone'])" class="mt-1.5 text-xs font-medium text-red-600">
                            {{ frontErrors['ketua.phone'] || friendlyError('ketua.phone', form.errors['ketua.phone']) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- 5. Data Anggota Kelompok -->
            <section v-if="form.tipe === 'kelompok'" class="glass-panel p-6 sm:p-8">
                <div class="mb-6 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-forest-600/10 text-sm font-bold text-forest-700">3</span>
                        <div>
                            <h2 class="font-display text-lg font-bold text-ink-900">Data Anggota Kelompok</h2>
                            <p class="text-xs text-ink-500">Tambahkan anggota lain selain ketua kelompok.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary shrink-0 disabled:cursor-not-allowed disabled:opacity-50" :disabled="!canAddMember" @click="addMember">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                        </svg>
                        + Tambah Anggota
                    </button>
                </div>

                <p v-if="quotaLimitMessage" class="mb-4 rounded-lg bg-amber-50 px-4 py-2.5 text-xs font-medium text-amber-700">
                    {{ quotaLimitMessage }}
                </p>

                <p v-if="frontErrors.members || form.errors.members" class="mb-4 rounded-lg bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600">
                    {{ frontErrors.members || form.errors.members }}
                </p>
                <p v-if="form.errors['members.0.name'] || form.errors['members.0.school'] || form.errors['members.0.major'] || form.errors['members.0.phone']" class="mb-4 rounded-lg bg-red-50 px-4 py-2.5 text-xs font-medium text-red-600">
                    Data anggota kelompok belum lengkap atau tidak valid.
                </p>

                <div v-if="members.length === 0" class="rounded-xl border border-dashed border-ink-300/60 py-10 text-center text-sm text-ink-500">
                    Belum ada anggota. Klik "+ Tambah Anggota" untuk menambahkan baris.
                </div>

                <div
                    v-for="(member, index) in members"
                    :key="index"
                    class="mb-6 rounded-xl border border-ink-300/40 bg-white/60 p-5"
                >
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-bold text-ink-900">Anggota {{ index + 1 }}</p>
                        <button type="button" class="inline-flex items-center gap-1 text-xs font-semibold text-status-danger hover:underline" @click="removeMember(index)">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="field-label" :for="`members.${index}.name`">Nama Lengkap</label>
                            <input :id="`members.${index}.name`" v-model="member.name" type="text" class="field-input" />
                            <p v-if="form.errors[`members.${index}.name`]" class="mt-1.5 text-xs font-medium text-red-600">
                                {{ form.errors[`members.${index}.name`] }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" :for="`members.${index}.nim`">NIM / NISN <span class="text-red-600">*</span></label>
                            <input :id="`members.${index}.nim`" v-model="member.nim" type="text" required class="field-input" />
                            <p v-if="friendlyError(`members.${index}.nim`, form.errors[`members.${index}.nim`])" class="mt-1.5 text-xs font-medium text-red-600">
                                {{ friendlyError(`members.${index}.nim`, form.errors[`members.${index}.nim`]) }}
                            </p>
                        </div>
                        <div>
                            <label class="field-label" :for="`members.${index}.phone`">No HP <span class="text-ink-400">(opsional)</span></label>
                            <input :id="`members.${index}.phone`" v-model="member.phone" type="text" inputmode="tel" class="field-input" />
                            <p v-if="friendlyError(`members.${index}.phone`, form.errors[`members.${index}.phone`])" class="mt-1.5 text-xs font-medium text-red-600">
                                {{ friendlyError(`members.${index}.phone`, form.errors[`members.${index}.phone`]) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 6. Upload Berkas -->
            <section class="glass-panel p-6 sm:p-8">
                <div class="mb-6 flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-forest-600/10 text-sm font-bold text-forest-700">{{ stepUpload }}</span>
                    <div>
                        <h2 class="font-display text-lg font-bold text-ink-900">Upload Berkas</h2>
                        <p class="text-xs text-ink-500">Unggah surat pengantar / proposal dalam satu file PDF.</p>
                    </div>
                </div>

                <label class="field-label" for="document">Surat Pengantar / Proposal (PDF)</label>
                <div class="flex flex-wrap items-center gap-3">
                    <label
                        for="document"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-ink-300 bg-white px-4 py-2.5 text-sm font-medium text-ink-700 transition hover:bg-surface focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2"
                    >
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        {{ fileName ? 'Ganti File' : 'Pilih File PDF' }}
                    </label>
                    <input id="document" type="file" accept=".pdf,application/pdf" class="hidden" @change="onFilePicked" />
                    <span v-if="fileName" class="text-sm font-medium text-forest-700">✓ {{ fileName }}</span>
                </div>
                <p class="mt-1.5 text-xs text-ink-500">Format PDF, maksimal 5MB.</p>
                <p v-if="frontErrors.document || form.errors.document" class="mt-1.5 text-xs font-medium text-red-600">
                    {{ frontErrors.document || form.errors.document }}
                </p>
            </section>

            <!-- Consent UU PDP & Submit -->
            <section class="glass-panel p-6 sm:p-8">
                <div class="mb-5 rounded-xl border border-amber-200/80 bg-amber-50/60 p-4">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            v-model="form.consent_pdp"
                            type="checkbox"
                            required
                            class="mt-1 h-4 w-4 shrink-0 rounded border-ink-300 text-forest-600 focus:ring-forest-500"
                        />
                        <span class="text-xs font-medium leading-relaxed text-ink-800">
                            Saya menyetujui pemrosesan data pribadi saya sesuai dengan Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi. <span class="text-red-600">*</span>
                        </span>
                    </label>
                    <p v-if="frontErrors.consent_pdp || form.errors.consent_pdp" class="mt-2 text-xs font-medium text-red-600">
                        {{ frontErrors.consent_pdp || form.errors.consent_pdp }}
                    </p>
                </div>

                <div class="flex flex-col items-center gap-3 sm:flex-row sm:justify-between">
                    <p v-if="form.tipe === 'kelompok'" class="text-xs text-ink-500">
                        Total slot dibutuhkan: ketua (1) + anggota ({{ members.length }}) = <strong>{{ 1 + members.length }}</strong>
                    </p>
                    <p v-else class="text-xs text-ink-500">Total slot dibutuhkan: 1 orang.</p>

                    <div class="flex flex-col items-center gap-2 sm:items-end">
                        <button
                            type="submit"
                            class="btn-primary px-8 py-3 disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="form.processing || !canSubmit"
                        >
                            {{ form.processing ? 'Mengirim...' : 'Kirim Pengajuan' }}
                        </button>
                        <p v-if="!form.consent_pdp" class="text-center text-xs font-medium text-amber-700 sm:text-right">
                            * Harap centang persetujuan UU PDP untuk mengaktifkan tombol kirim.
                        </p>
                        <p v-else-if="availability?.available === false" class="text-center text-xs font-semibold text-red-600 sm:text-right">
                            Kuota tidak tersedia untuk periode ini. Ubah bidang atau rentang tanggal untuk mengaktifkan tombol kirim.
                        </p>
                        <p v-else-if="form.tipe === 'kelompok' && availability?.slot_tersedia !== undefined && (1 + members.length) > availability.slot_tersedia" class="text-center text-xs font-semibold text-red-600 sm:text-right">
                            Total rombongan ({{ 1 + members.length }} orang) melebihi sisa kuota periode ini ({{ availability.slot_tersedia }} slot). Kurangi anggota kelompok.
                        </p>
                    </div>
                </div>
            </section>
        </form>
    </AppLayout>
</template>