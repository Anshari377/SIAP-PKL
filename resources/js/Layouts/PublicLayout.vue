<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface">
        <!-- Public Header -->
        <header class="sticky top-0 z-30 border-b border-ink-300/40 bg-white/80 backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5 sm:px-6">
                <Link :href="route('katalog.index')" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-b from-forest-950 via-forest-800 to-forest-600 text-gold-400 shadow-inner">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="leading-tight">
                        <span class="block font-display text-base font-bold tracking-tight text-ink-900">SIAP-PKL</span>
                        <span class="block text-[9px] font-semibold uppercase tracking-widest text-forest-700">Diskominfo Samarinda</span>
                    </div>
                </Link>

                <div class="flex items-center gap-2">
                    <Link :href="route('login')" class="btn-primary px-5 py-2.5 text-sm">
                        Login
                    </Link>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Public Footer -->
        <footer class="border-t border-ink-300/40 bg-white/60 py-6">
            <div class="mx-auto max-w-6xl px-4 text-center text-xs text-ink-500 sm:px-6">
                © 2026 Dinas Komunikasi dan Informatika Provinsi Kalimantan Timur
            </div>
        </footer>
    </div>
</template>