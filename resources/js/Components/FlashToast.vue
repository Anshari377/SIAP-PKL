<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const toasts = ref([]);
let seed = 0;
const timers = new Map();

const dismiss = (id) => {
    const timer = timers.get(id);
    if (timer) {
        clearTimeout(timer);
        timers.delete(id);
    }
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
};

const push = (type, message) => {
    const id = ++seed;
    toasts.value.push({ id, type, message });
    timers.set(id, setTimeout(() => dismiss(id), 5000));
};

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([success, error]) => {
        if (success) push('success', success);
        if (error) push('error', error);
    },
);

onBeforeUnmount(() => timers.forEach((timer) => clearTimeout(timer)));
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[80] flex flex-col items-center gap-3 px-4 sm:inset-x-auto sm:right-4 sm:items-end">
        <transition-group name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl p-4 shadow-lg ring-1 ring-white/20"
                :class="
                    toast.type === 'error'
                        ? 'bg-gradient-to-r from-red-600 to-status-danger text-white'
                        : 'bg-gradient-to-r from-forest-600 to-forest-700 text-white'
                "
            >
                <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white/15">
                    <svg
                        v-if="toast.type === 'error'"
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <svg
                        v-else
                        viewBox="0 0 24 24"
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                </span>
                <p class="flex-1 text-sm font-medium leading-snug">{{ toast.message }}</p>
                <button
                    type="button"
                    class="shrink-0 rounded-full p-1 text-white/70 transition hover:bg-white/10 hover:text-white"
                    aria-label="Tutup notifikasi"
                    @click="dismiss(toast.id)"
                >
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="6" y1="6" x2="18" y2="18" />
                        <line x1="18" y1="6" x2="6" y2="18" />
                    </svg>
                </button>
            </div>
        </transition-group>
    </div>
</template>