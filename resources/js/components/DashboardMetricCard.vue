<script setup lang="ts">
import type { Component, HTMLAttributes } from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

type Props = {
    title: string;
    value: number | string;
    description: string;
    icon: Component;
    accentClass?: HTMLAttributes['class'];
    class?: HTMLAttributes['class'];
};

const props = withDefaults(defineProps<Props>(), {
    accentClass: 'border-primary/15 bg-primary/10 text-primary',
});
</script>

<template>
    <Card
        :class="
            cn(
                'gap-0 overflow-hidden border-border bg-card py-0 shadow-none backdrop-blur-none',
                props.class,
            )
        "
    >
        <CardContent class="flex items-start justify-between gap-4 p-6">
            <div class="space-y-2">
                <p class="text-sm font-medium text-muted-foreground">
                    {{ title }}
                </p>
                <p
                    class="text-3xl font-semibold tracking-tight text-foreground"
                >
                    {{ value }}
                </p>
                <p class="max-w-[26ch] text-sm leading-6 text-muted-foreground">
                    {{ description }}
                </p>
            </div>

            <div
                :class="
                    cn(
                        'flex size-11 shrink-0 items-center justify-center rounded-2xl border',
                        props.accentClass,
                    )
                "
            >
                <component :is="icon" class="size-5" />
            </div>
        </CardContent>
    </Card>
</template>
