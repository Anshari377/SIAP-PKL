<script setup>
defineProps({
    steps: { type: Array, default: () => [] },
});
</script>

<template>
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

                <!-- Revision: lingkaran amber dengan tanda seru -->
                <div v-else-if="step.status === 'revision'" class="grid h-6 w-6 place-items-center rounded-full bg-amber-500 text-white ring-4 ring-amber-400/20">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3">
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
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
                        step.status === 'revision' ? 'font-bold text-amber-700' : '',
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
</template>