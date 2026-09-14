<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const homeHref = computed(() => {
    const roles = page.props.auth?.roles ?? [];
    if (roles.includes('super_admin')) return route('superadmin.dashboard');
    if (roles.includes('agency_admin')) return route('admin.dashboard');
    if (page.props.auth?.user) return route('home');
    return route('katalog.index');
});
</script>

<template>
    <Head title="Halaman Tidak Ditemukan" />

    <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-surface p-4 sm:p-6">
        <!-- Background Decorations -->
        <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-forest-500/10 blur-3xl" />
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-gold-500/15 blur-3xl" />

        <!-- Ulap Doyo Batik Watermark -->
        <svg class="pointer-events-none absolute inset-0 h-full w-full text-forest-600 opacity-[0.05]" aria-hidden="true">
            <defs>
                <pattern id="ulap-doyo-error" width="60" height="60" patternUnits="userSpaceOnUse">
                    <path d="M30 0 L60 30 L30 60 L0 30 Z" fill="none" stroke="currentColor" stroke-width="1" />
                    <path d="M30 10 L50 30 L30 50 L10 30 Z" fill="none" stroke="currentColor" stroke-width="0.8" />
                    <path d="M30 20 L40 30 L30 40 L20 30 Z" fill="none" stroke="currentColor" stroke-width="0.6" />
                    <line x1="0" y1="0" x2="60" y2="60" stroke="currentColor" stroke-width="0.5" />
                    <line x1="60" y1="0" x2="0" y2="60" stroke="currentColor" stroke-width="0.5" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#ulap-doyo-error)" />
        </svg>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-3xl border border-ink-300/40 bg-white p-8 text-center shadow-[0_20px_50px_rgba(15,74,48,0.10)] backdrop-blur-xl sm:p-12">
            <!-- Top Accent Bar -->
            <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-forest-800 via-gold-400 to-forest-600" />

            <!-- Branding -->
            <div class="mb-8 flex items-center justify-center gap-3">
                <div class="grid h-11 w-11 place-items-center rounded-xl bg-forest-900 text-gold-400 shadow-md">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="text-left">
                    <p class="font-display text-sm font-bold tracking-tight text-ink-900">SIAP-PKL</p>
                    <p class="text-[10px] font-medium uppercase tracking-wider text-forest-700">Diskominfo Kaltim</p>
                </div>
            </div>

            <!-- Icon -->
            <div class="mx-auto mb-5 grid h-24 w-24 place-items-center rounded-full bg-forest-600/10 text-forest-700">
                <svg viewBox="0 0 24 24" class="h-12 w-12" fill="none" stroke="currentColor" stroke-width="1.6">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
                    <line x1="11" y1="8" x2="11" y2="11" stroke-linecap="round" />
                    <line x1="11" y1="14.01" x2="11.01" y2="14" stroke-linecap="round" />
                </svg>
            </div>

            <h1 class="font-display text-4xl font-bold text-ink-900">404</h1>
            <h2 class="mt-2 font-display text-lg font-bold text-ink-900">Halaman Tidak Ditemukan</h2>
            <p class="mt-3 text-sm leading-relaxed text-ink-500">
                Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan. Silakan kembali ke beranda untuk melanjutkan aktivitas Anda.
            </p>

            <Link :href="homeHref" class="btn-primary mt-8 w-full">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                Kembali ke Beranda
            </Link>
        </div>
    </main>
</template>