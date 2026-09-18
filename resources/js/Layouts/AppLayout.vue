<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({ title: { type: String, default: '' } });

const page = usePage();
const sidebarOpen = ref(true);
const mobileOpen = ref(false);
const avatarFailed = ref(false);

watch(
    () => page.url,
    () => {
        mobileOpen.value = false;
    },
);

const isAdmin = computed(() => (page.props.auth?.roles ?? []).includes('agency_admin'));

const studentNav = [
    { label: 'Home', href: route('home'), active: 'home', icon: 'home' },
    { label: 'Bidang PKL', href: route('bidang.index'), active: 'bidang', icon: 'briefcase' },
    { label: 'Pengajuan PKL', href: route('pengajuan.index'), active: 'pengajuan', icon: 'file-text' },
    { label: 'Kelompok Saya', href: route('kelompok.index'), active: 'kelompok', icon: 'users' },
    { label: 'Riwayat', href: route('riwayat.index'), active: 'riwayat', icon: 'clock' },
    { label: 'Profile Saya', href: route('profile.edit'), active: 'profile', icon: 'user' },
];

const adminNav = [
    { label: 'Dashboard', href: route('admin.dashboard'), active: 'admin.dashboard', icon: 'home' },
    { label: 'Bidang PKL', href: route('admin.bidang.index'), active: 'admin.bidang', icon: 'briefcase' },
    { label: 'Pengajuan Masuk', href: route('admin.pengajuan.index'), active: 'admin.pengajuan', icon: 'file-text' },
    { label: 'Peserta PKL', href: route('admin.peserta.index'), active: 'admin.peserta', icon: 'users' },
    { label: 'Profile Saya', href: route('admin.profile.edit'), active: 'admin.profile', icon: 'user' },
];

const filteredNav = computed(() => {
    const nav = isAdmin.value ? adminNav : studentNav;
    return nav.filter((item) => {
        if (item.label === 'Kelompok Saya') {
            return page.props.auth?.user?.tipe_pendaftaran === 'kelompok';
        }
        return true;
    });
});

const current = page.props.activeNav ?? '';
</script>

<template>
    <div class="flex min-h-screen bg-surface">
        <!-- Mobile Backdrop -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-30 bg-ink-950/60 backdrop-blur-sm md:hidden"
            @click="mobileOpen = false"
        />

        <!-- Sidebar - Dark Green Gradient with Ulap Doyo Batik Pattern -->
        <aside
            :class="[
                mobileOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarOpen ? 'md:w-64' : 'md:w-[76px]',
            ]"
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col overflow-visible bg-gradient-to-b from-forest-950 via-forest-800 to-forest-600 text-white transition-all duration-200 md:static md:translate-x-0"
        >
            <!-- Ulap Doyo Batik SVG Pattern Watermark -->
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.07]" aria-hidden="true">
                <defs>
                    <pattern id="ulap-doyo-sidebar" width="48" height="48" patternUnits="userSpaceOnUse">
                        <path d="M24 0 L48 24 L24 48 L0 24 Z" fill="none" stroke="currentColor" stroke-width="1" />
                        <path d="M24 8 L40 24 L24 40 L8 24 Z" fill="none" stroke="currentColor" stroke-width="0.8" />
                        <path d="M24 14 L34 24 L24 34 L14 24 Z" fill="none" stroke="currentColor" stroke-width="0.6" />
                        <line x1="0" y1="0" x2="48" y2="48" stroke="currentColor" stroke-width="0.5" />
                        <line x1="48" y1="0" x2="0" y2="48" stroke="currentColor" stroke-width="0.5" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#ulap-doyo-sidebar)" class="text-gold-400" />
            </svg>

            <!-- Sidebar Header / Logo -->
            <div class="relative flex items-center gap-3 px-5 py-5 border-b border-white/10">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/10 text-gold-400 shadow-inner">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div v-if="sidebarOpen || mobileOpen" class="flex flex-col">
                    <span class="font-display text-base font-bold tracking-tight text-white">SIAP-PKL</span>
                    <span class="text-[10px] text-gold-400 font-medium tracking-wider uppercase">Diskominfo Samarinda</span>
                </div>
                <button
                    v-if="mobileOpen"
                    type="button"
                    class="ml-auto grid h-8 w-8 place-items-center rounded-lg text-white/70 hover:bg-white/10 hover:text-white md:hidden"
                    @click="mobileOpen = false"
                    aria-label="Tutup menu"
                >
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex flex-1 flex-col px-3 py-4 overflow-y-auto">
                <Link
                    v-for="item in filteredNav"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        current === item.active
                            ? 'bg-white/10 text-white font-semibold border-l-4 border-gold-400'
                            : 'text-white/70 hover:bg-white/5 hover:text-white border-l-4 border-transparent',
                    ]"
                    class="flex items-center gap-3 px-4 py-3 text-sm transition-colors duration-200"
                    @click="mobileOpen = false"
                >
                    <span class="grid h-5 w-5 shrink-0 place-items-center">
                        <template v-if="item.icon === 'home'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                            </svg>
                        </template>
                        <template v-else-if="item.icon === 'briefcase'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/>
                            </svg>
                        </template>
                        <template v-else-if="item.icon === 'file-text'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                        </template>
                        <template v-else-if="item.icon === 'users'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
                            </svg>
                        </template>
                        <template v-else-if="item.icon === 'clock'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                        </template>
                        <template v-else-if="item.icon === 'user'">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </template>
                    </span>
                    <span v-if="sidebarOpen || mobileOpen" class="truncate">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Toggle Sidebar (desktop) -->
            <button
                class="relative m-3 hidden items-center justify-center rounded-lg py-2 text-white/60 hover:bg-white/5 hover:text-white md:flex"
                @click="sidebarOpen = !sidebarOpen"
            >
                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" />
                </svg>
            </button>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-1 flex-col min-w-0">
            <!-- Floating Glass Header -->
            <header class="glass-panel m-2 flex items-center justify-between gap-2 px-3 py-3 sm:m-3 sm:px-6 sm:py-3.5">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <button
                        type="button"
                        class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-ink-600 hover:bg-ink-100 md:hidden"
                        @click="mobileOpen = true"
                        aria-label="Buka menu"
                    >
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>
                        </svg>
                    </button>
                    <h1 class="truncate font-display text-base font-semibold text-ink-900 sm:text-lg">{{ title }}</h1>
                </div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <Link :href="isAdmin ? route('admin.profile.edit') : route('profile.edit')" class="flex items-center gap-2 group sm:gap-3">
                        <div class="grid h-9 w-9 place-items-center rounded-full bg-forest-700 text-xs font-semibold text-white shadow-sm transition group-hover:bg-forest-600 overflow-hidden shrink-0">
                            <img
                                v-if="page.props.auth?.user?.avatar && !avatarFailed"
                                :src="page.props.auth.user.avatar"
                                :alt="page.props.auth?.user?.name || 'User Avatar'"
                                class="h-full w-full object-cover"
                                @error="avatarFailed = true"
                            />
                            <span v-else>
                                {{ (page.props.auth?.user?.name ?? 'M A').split(' ').map(w => w[0]).slice(0,2).join('') }}
                            </span>
                        </div>
                        <span class="hidden text-sm font-medium text-ink-700 group-hover:text-forest-700 transition sm:inline">
                            {{ page.props.auth?.user?.name ?? 'Mahasiswa' }}
                        </span>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="text-xs font-medium text-ink-500 hover:text-status-danger transition">
                        Keluar
                    </Link>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                <div v-if="page.props.flash?.error" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                    {{ page.props.flash.error }}
                </div>
                <div v-if="page.props.flash?.success" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 shadow-sm">
                    {{ page.props.flash.success }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
