<script setup>
import { Head, Link } from '@inertiajs/vue3';

const handleGoogleLogin = () => {
    window.location.href = route('auth.google');
};
</script>

<template>
    <Head title="Masuk - SIAP-PKL Diskominfo Samarinda" />

    <main class="relative grid min-h-screen bg-surface lg:grid-cols-12 overflow-hidden">
        <!-- Panel Kiri (Hidden di Mobile - 7 Cols) - Rich Dark Forest Gradient + Geometric Decorations.
             Rectangular panel; straight vertical divider against the right panel. -->
        <section
            class="relative hidden overflow-hidden bg-gradient-to-br from-forest-950 via-forest-900 to-forest-800 p-12 text-white lg:col-span-7 lg:flex lg:flex-col lg:justify-between"
        >
            <!-- Ambient Background Radial Lighting Overlays -->
            <div class="pointer-events-none absolute -top-24 -left-24 h-96 w-96 rounded-full bg-gold-500/15 blur-3xl" />
            <div class="pointer-events-none absolute -bottom-24 -right-24 h-[30rem] w-[30rem] rounded-full bg-forest-500/20 blur-3xl" />
            <div class="pointer-events-none absolute top-1/2 left-1/3 h-80 w-80 -translate-y-1/2 rounded-full bg-gold-400/10 blur-2xl" />

            <!-- ========== GEOMETRIC DECORATIVE ELEMENTS (z-[1], below batik watermark z-[2]) ========== -->
            <div class="pointer-events-none absolute inset-0 z-[1]" aria-hidden="true">
                <!-- 1. Outline Circles — scattered at corners, partially clipped by overflow -->
                <!-- Top-left: large outline circle, clipped at top-left edge -->
                <div class="absolute -top-16 -left-16 h-56 w-56 rounded-full border border-white/[0.25]" />
                <!-- Top-left: medium outline circle, slightly offset -->
                <div class="absolute top-10 -left-8 h-32 w-32 rounded-full border border-forest-400/[0.30]" />
                <!-- Bottom-left: medium outline circle, clipped at bottom edge -->
                <div class="absolute -bottom-12 left-8 h-40 w-40 rounded-full border border-white/[0.22]" />
                <!-- Mid-right area: large outline circle, tucked inside the wave curve so it isn't oddly clipped -->
                <div class="absolute top-[40%] right-4 h-64 w-64 rounded-full border border-white/[0.20]" />

                <!-- Gold solid accent dots — small, warm accent -->
                <div class="absolute top-20 right-16 h-3 w-3 rounded-full bg-gold-400/90" />
                <div class="absolute top-[38%] left-6 h-2.5 w-2.5 rounded-full bg-gold-500" />
                <div class="absolute bottom-28 right-[30%] h-2 w-2 rounded-full bg-gold-400/80" />

                <!-- 2. Dot Grid Pattern — bottom-left corner, subtle texture -->
                <svg class="absolute bottom-6 left-6 h-32 w-32 text-white/[0.55]">
                    <defs>
                        <pattern id="dot-grid" x="0" y="0" width="16" height="16" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1" fill="currentColor" />
                        </pattern>
                    </defs>
                    <rect width="128" height="128" fill="url(#dot-grid)" />
                </svg>

                <!-- 3. Large Curved Blob / Half-Circle — bottom-right of left panel -->
                <div class="absolute -bottom-32 -right-32 h-[28rem] w-[28rem] rounded-full bg-gradient-to-tl from-gold-500/[0.28] via-forest-500/[0.20] to-transparent" />
                <div class="absolute -bottom-24 -right-24 h-80 w-80 rounded-full border border-gold-400/[0.22]" />

                <!-- 4. Vertical Pill / Capsule Lines — upper area, white transparent accents -->
                <div class="absolute top-12 right-[22%] h-20 w-[3px] rounded-full bg-white/[0.22]" />
                <div class="absolute top-16 right-[18%] h-14 w-[3px] rounded-full bg-white/[0.18]" />
                <div class="absolute top-8 right-[26%] h-10 w-[2px] rounded-full bg-white/[0.20]" />
            </div>
            <!-- ========== /GEOMETRIC DECORATIVE ELEMENTS ========== -->

            <!-- Ulap Doyo Batik SVG Pattern Watermark (z-[2], above geometric decorations but below content z-10) -->
            <svg
                class="pointer-events-none absolute inset-0 z-[2] h-full w-full text-gold-400/15 opacity-20 transition-opacity duration-500"
                aria-hidden="true"
            >
                <defs>
                    <pattern id="login-ulap-doyo-rich" width="60" height="60" patternUnits="userSpaceOnUse">
                        <!-- Outer Diamond Motif -->
                        <path d="M30 0 L60 30 L30 60 L0 30 Z" fill="none" stroke="currentColor" stroke-width="1.2" />
                        <!-- Inner Layered Diamond -->
                        <path d="M30 10 L50 30 L30 50 L10 30 Z" fill="none" stroke="currentColor" stroke-width="0.8" stroke-dasharray="3 2" />
                        <!-- Center Core Star Diamond -->
                        <path d="M30 20 L40 30 L30 40 L20 30 Z" fill="none" stroke="currentColor" stroke-width="0.6" />
                        <!-- Diagonal Weave Lines -->
                        <line x1="0" y1="0" x2="60" y2="60" stroke="currentColor" stroke-width="0.5" />
                        <line x1="60" y1="0" x2="0" y2="60" stroke="currentColor" stroke-width="0.5" />
                        <!-- Corner Cross Accent -->
                        <circle cx="30" cy="30" r="1.5" fill="currentColor" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#login-ulap-doyo-rich)" />
            </svg>

            <!-- Header Branding -->
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 p-2 text-gold-400 backdrop-blur-md border border-white/15 shadow-inner">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <span class="block font-display text-lg font-bold tracking-tight text-white">SIAP-PKL</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-widest text-gold-400">Diskominfo Samarinda</span>
                </div>
            </div>

            <!-- Content Area - Official Typography -->
            <div class="relative z-10 my-auto max-w-2xl py-10">
                <h1 class="font-official text-3xl xl:text-4xl font-bold leading-snug tracking-wide text-white">
                    Pemerintah Provinsi Kalimantan Timur
                </h1>

                <div class="my-6 h-1 w-20 rounded-full bg-gradient-to-r from-gold-400 to-gold-500/0" />

                <p class="text-sm xl:text-base leading-relaxed text-white/80 max-w-lg font-sans">
                    Fasilitas pendaftaran, verifikasi berkas, hingga pemantauan status pendaftaran PKL di lingkungan Dinas Komunikasi dan Informatika Provinsi Kalimantan Timur.
                </p>
            </div>

            <!-- Footer Left -->
            <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-4 text-xs text-white/60">
                <p>© 2026 Dinas Komunikasi dan Informatika Provinsi Kalimantan Timur</p>
            </div>
        </section>

        <!-- Panel Kanan: Form Solid / Elevated Glass Container (5 Cols) -->
        <section class="flex flex-col items-center justify-center px-6 py-12 sm:px-10 lg:col-span-5 lg:px-12 xl:px-16 bg-surface">
            <!-- Mobile Header Logo (Only shown on small screens) -->
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-forest-900 text-gold-400 shadow-md">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-display text-lg font-bold text-ink-900">SIAP-PKL</h2>
                    <p class="text-xs font-medium text-forest-700 uppercase tracking-wider">Diskominfo Samarinda</p>
                </div>
            </div>

            <!-- Main Form Card Container -->
            <div class="relative w-full max-w-md overflow-hidden rounded-3xl border border-ink-300/40 bg-white p-6 shadow-[0_20px_50px_rgba(15,74,48,0.08)] backdrop-blur-xl sm:p-10">
                <!-- Top Color Accent Bar -->
                <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-forest-800 via-gold-400 to-forest-600" />

                <div class="mb-8">
                    <h2 class="font-display text-2xl font-bold tracking-tight text-ink-900 sm:text-3xl">
                        Selamat Datang
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed text-ink-500">
                        Masuk dengan akun Google Anda untuk mengakses portal pendaftaran & alur PKL.
                    </p>
                </div>

                <!-- Action Section: Google Login Button -->
                <div class="space-y-4">
                    <button
                        type="button"
                        @click="handleGoogleLogin"
                        class="group relative flex w-full items-center justify-center gap-3.5 rounded-xl border border-ink-300/80 bg-white px-6 py-4 text-sm font-semibold text-ink-900 shadow-sm transition-all duration-200 hover:border-forest-600 hover:bg-forest-50/40 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-forest-500 focus:ring-offset-2"
                    >
                        <!-- Google SVG Icon -->
                        <svg class="h-5 w-5 shrink-0 transition-transform duration-200 group-hover:scale-105" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span>Login dengan Google</span>
                        <svg viewBox="0 0 24 24" class="h-4 w-4 text-ink-300 transition-transform duration-200 group-hover:translate-x-1 group-hover:text-forest-600" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>

                    <Link :href="route('katalog.index')" class="block text-center text-xs font-medium text-forest-700 hover:underline">
                        Lihat Katalog Bidang PKL tanpa login
                    </Link>
                </div>

                <!-- Legal Footer -->
                <div class="mt-8 border-t border-ink-300/30 pt-6">
                    <p class="text-center text-[11px] leading-relaxed text-ink-500">
                        Dengan melanjutkan, Anda menyetujui <a href="#" class="font-medium text-forest-700 underline decoration-forest-700/30 hover:decoration-forest-700">Ketentuan Layanan</a> & <a href="#" class="font-medium text-forest-700 underline decoration-forest-700/30 hover:decoration-forest-700">Kebijakan Privasi</a> Diskominfo Samarinda.
                    </p>
                </div>
            </div>
        </section>
    </main>
</template>
