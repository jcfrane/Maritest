<script setup lang="ts">
import { Card } from '@/components/ui/card';

type Props = {
    panelClass?: string;
    headerClass?: string;
    bodyClass?: string;
    footerClass?: string;
};

withDefaults(defineProps<Props>(), {
    panelClass: '',
    headerClass: '',
    bodyClass: '',
    footerClass: '',
});
</script>

<template>
    <Card
        class="gap-0 overflow-hidden border-border bg-card py-0 shadow-none backdrop-blur-none"
        :class="panelClass"
    >
        <div
            v-if="$slots.header"
            class="border-b border-border/80 bg-muted/10 px-4 py-4 md:px-6"
            :class="headerClass"
        >
            <slot name="header" />
        </div>

        <div v-if="$slots.default" :class="bodyClass">
            <slot />
        </div>

        <div
            v-if="$slots.footer"
            class="border-t border-border/80 px-4 py-4 md:px-6"
            :class="footerClass"
        >
            <slot name="footer" />
        </div>
    </Card>
</template>
