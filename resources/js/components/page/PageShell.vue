<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    contentClass?: string;
};

withDefaults(defineProps<Props>(), {
    description: undefined,
    contentClass: '',
});
</script>

<template>
    <div
        class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col rounded-2xl p-4 md:p-6"
        :class="contentClass"
    >
        <header
            class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between"
        >
            <div class="space-y-1">
                <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                    {{ title }}
                </h1>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <div
                v-if="$slots.actions"
                class="flex items-center gap-3 self-start lg:self-auto"
            >
                <slot name="actions" />
            </div>
        </header>

        <slot />
    </div>
</template>
